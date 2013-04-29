<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Manages the date- and their time slots, persists them and their changes to cache.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Service_SlotService implements t3lib_Singleton {

	//constants
	const DATESLOT_KEY_FORMAT = 'Ymd';

	/**
	 * Extension name
	 *
	 * @var string
	 */
	protected $extensionName;

	/**
	 * DateSlot storage array
	 *
	 * @var array
	 */
	protected $dateSlots;

	/**
	 * Single DateSlot storage array
	 *
	 * @var array
	 */
	protected $singleDateSlots;

	/**
	 * appointmentRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_AppointmentRepository
	 */
	protected $appointmentRepository;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		$this->extensionName = 'appointments';
		$this->dateSlots = array();
	}

	/**
	 * injectAppointmentRepository
	 *
	 * @param Tx_Appointments_Domain_Repository_AppointmentRepository $appointmentRepository
	 * @return void
	 */
	public function injectAppointmentRepository(Tx_Appointments_Domain_Repository_AppointmentRepository $appointmentRepository) {
		$this->appointmentRepository = $appointmentRepository;
	}

	/**
	 * Returns the dateSlot storage of the specified type.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type domain model object instance
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @param integer $expireMinutes Number of minutes in which unfinished appointments expire unless finished
	 * @return Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_DateSlot>
	 */
	public function getDateSlots(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, $expireMinutes) {
		$typeUid = $type->getUid();
		if ($this->clearExpiredAppointmentTimeSlots($type,$agenda,$expireMinutes) || !isset($this->dateSlots[$typeUid])) {
			$this->dateSlots[$typeUid] = $this->getStorageObject($type, $agenda);
		}

		return $this->dateSlots[$typeUid];
	}

	/**
	 * Get dateslots and include the timeslots in use due to the current appointment
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment Current appointment
	 * @return Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_DateSlot>
	 */
	public function getDateSlotsIncludingCurrent(Tx_Appointments_Domain_Model_Appointment $appointment) {
		$type = $appointment->getType();
		$agenda = $appointment->getAgenda();
		$uid = $appointment->getUid();

		$dateSlotStorage = $this->buildStorageObject($type, $agenda, $uid);

		$dateTime = new DateTime();
		$dateTime->setTimestamp($appointment->getBeginTime()->getTimestamp());
		$key = $dateTime->format(self::DATESLOT_KEY_FORMAT);
		$dateSlot = $dateSlotStorage->getObjectByKey($key);

		//in case the current appointment is out of range for the dateSlotStorage, this will include its date and timeslot regardless
		if ($dateSlot === FALSE) {
			$extraDateSlot = $this->buildSingleStorageObject($type,$agenda,$dateTime,$uid,1);
			$dateSlot = $extraDateSlot->getObjectByKey($key);
			if ($dateSlot !== FALSE) {
				$dateSlotStorage->attach($dateSlot);
			}
		}
		return $dateSlotStorage;
	}

	#@FIXME blabla
	#@TODO doc
	public function getSingleDateSlot(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, $expireMinutes, $timestamp, $excludeAppointment = 0) {
		$typeUid = $type->getUid();
		$dateTime = new DateTime();
		$dateTime->setTimestamp($timestamp);
		$dateSlotKey = $dateTime->format(self::DATESLOT_KEY_FORMAT);
		#@FIXME dit is enorm slecht performance-wise, aangezien hij dit voor elke type aanroept terwijl het maar 1x hoeft natuurlijk
		#@FIXME resetStorage moet ook single storage doen
		#@FIXME dit moet net zo werken als getDateSlots()
		if (
				!$this->clearExpiredAppointmentTimeSlots($type,$agenda,$expireMinutes) &&
				(
					(
						isset($this->dateSlots[$typeUid]) &&
						$dateSlotStorage = $this->dateSlots[$typeUid]
					) ||
					($dateSlotStorage = $this->getStorageObjectFromCache($type, $agenda)) !== FALSE
				)
		) {

			$dateSlot = $dateSlotStorage->getObjectByKey($dateSlotKey);
			if ($dateSlot !== FALSE) {
				$dateSlotStorage = new Tx_Appointments_Persistence_KeyObjectStorage();
				$dateSlotStorage->attach($dateSlot);
				#@FIXME assign to singleDateSlots
				return $dateSlotStorage;
			}
		}

		$this->singleDateSlots[$typeUid][$dateSlotKey] = $this->buildSingleStorageObject($type,$agenda,$dateTime,$excludeAppointment);
		return $this->singleDateSlots[$typeUid][$dateSlotKey];
	}

	/**
	 * Gets timeslots for the day of the appointment, from the date slot storage.
	 * If the dateslot is not available for the appointment, will return FALSE,
	 * unless the appointment is unfinished.
	 *
	 * @param Tx_Appointments_Persistence_KeyObjectStorage $dateSlotStorage Contains date slots
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment Appointment to get the dateslot for
	 * @return boolean|Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_TimeSlot>
	 */
	public function getTimeSlots(Tx_Appointments_Persistence_KeyObjectStorage $dateSlotStorage, Tx_Appointments_Domain_Model_Appointment $appointment) {
		$date = $appointment->getBeginTime();
		$key = $date->format(self::DATESLOT_KEY_FORMAT);
		$dateSlot = $dateSlotStorage->getObjectByKey($key);

		if ($dateSlot === FALSE) {
			//if the appointment is anything but finished, at least provide a dummy dateslot for its label
			if(!$appointment->_isNew() && $appointment->getCreationProgress() !== Tx_Appointments_Domain_Model_Appointment::FINISHED) { #@FIXME this isn't the most secure option, anyone can alter the form's beginTime value and thus get away with it, at least until he tries to save
				$dateSlot = new Tx_Appointments_Domain_Model_DateSlot();
				$dateSlot->setTimestamp($date->getTimestamp());
				$dateSlot->setKey($key);
				$dateSlot->setDayName($date->format('l'));
				$dayShort = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.day_s'.$date->format('N'), $this->extensionName);
				$dateSlot->setLabel($dayShort . ' ' . $date->format('d-m-Y'));
				$dateSlotStorage->attach($dateSlot);
			} else {
				return FALSE;
			}
		}

		return $dateSlot->getTimeSlots();
	}

	/**
	 * Checks if the timeslot for the appointment is allowed. If the timeslot is taken,
	 * will return FALSE, unless the appointment was already created but not finished.
	 *
	 * @param Tx_Appointments_Persistence_KeyObjectStorage $timeSlots The time slot storage
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to check the timeslot for
	 * @return boolean
	 */
	public function isTimeSlotAllowed(Tx_Appointments_Persistence_KeyObjectStorage $timeSlots, Tx_Appointments_Domain_Model_Appointment $appointment) { #@TODO seriously reconsider this function, because it seems utterly useless other than adding a missing timeslot to timeslots
		$timestamp = $appointment->getBeginTime()->getTimestamp();
		$timeSlot = $timeSlots->getObjectByKey(strftime('%Y-%m-%d %H:%M:%S',$timestamp));

		#if ($appointment->getCreationProgress() !== Tx_Appointments_Domain_Model_Appointment::FINISHED) { #@FIXME this isn't the most secure option, anyone can alter the form's beginTime value and thus get away with it, at least until he tries to save
			if ($timeSlot === FALSE) {
				$timeSlots->attach($this->createTimeSlot($timestamp));
			}
			return TRUE;
		#}


		#if ($timeSlot !== FALSE) {
		#	return TRUE;
		#}

		#return FALSE;
	}

	/**
	 * Clears expired appointments to free up timeslots, and returns whether there are any changes.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type to which the expired appointments belong to
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @param integer $expireMinutes Number of minutes in which unfinished appointments expire unless finished
	 * @return boolean TRUE on change, FALSE on no change
	 */
	protected function clearExpiredAppointmentTimeSlots(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, $expireMinutes) {
		$temp = $this->appointmentRepository->findExpiredUnfinished($expireMinutes);

		if (!empty($temp)) {
			foreach ($temp as $appointment) {
				$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::EXPIRED);
				$this->appointmentRepository->update($appointment);
			}
			$this->resetStorageObject($type,$agenda);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Initializes the Tx_Appointments_Persistence_KeyObjectStorage properties and builds its content.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type domain model object instance
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @param integer $excludeAppointment UID of an appointment that is ignored when building the storage
	 * @return Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_DateSlot>
	 */
	protected function buildStorageObject(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, $excludeAppointment = 0) {
		$dateSlotStorage = new Tx_Appointments_Persistence_KeyObjectStorage();

		$dateTime = $this->getFirstAvailableTime($type, $agenda);

		$maxDaysAhead = $type->getMaxDaysForward();
		$this->createDateSlots($dateSlotStorage, $dateTime, $type, $agenda, $maxDaysAhead, $excludeAppointment);

		return $dateSlotStorage;
	}

	/**
	 * Initializes the Tx_Appointments_Persistence_KeyObjectStorage properties and builds its content for a SINGLE date slot.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Type domain model object instance
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @param DateTime $dateTime Represents the date for which to retrieve the dateslot and time from which to retrieve the timeslots
	 * @param integer $excludeAppointment UID of an appointment that is ignored when building the storage
	 * @param boolean $disregardConditions If TRUE, disregards the firstAvailableTime conditions
	 * @return Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_DateSlot>
	 */
	protected function buildSingleStorageObject(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, DateTime $dateTime, $excludeAppointment = 0, $disregardConditions = FALSE) {
		$dateSlotStorage = new Tx_Appointments_Persistence_KeyObjectStorage();

		$firstAvailableTime = $this->getFirstAvailableTime($type, $agenda)->getTimestamp();
		if (
				$disregardConditions || (
					$firstAvailableTime < $dateTime->modify('+1 day')->setTime(0,0)->getTimestamp() &&
					$dateTime->modify('-1 day')
				)
		) {
			$this->createDateSlots($dateSlotStorage, $dateTime, $type, $agenda, 1, $excludeAppointment);
		}

		return $dateSlotStorage;
	}

	/**
	 * Alters an existing dateslot storage object to match the current time.
	 *
	 * !CURRENTLY UNUSED! Keeping it around in case it proves useful again.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type domain model object instance
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @return Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_DateSlot>
	 */
	protected function alterStorageObject(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda) {
		$typeUid = $type->getUid();
		$dateSlotStorage = $this->dateSlots[$typeUid];
		$firstAvailableTimestamp = $this->getFirstAvailableTime($type, $agenda)->getTimestamp();
		$dateSlotStorage->rewind();
		//remove all dateslots up to the first available one or until none are left (dateslots contain a timestamp of their first minute)
		while (($dateSlot = $dateSlotStorage->current()) !== FALSE && $dateSlot->getTimestamp() < $firstAvailableTimestamp) {
			$dateSlotStorage->detach($dateSlot);
			$dateSlotStorage->next();
		}
		if ($dateSlot !== FALSE) { //the first available day is already present in the storage
			$timeSlots = $dateSlot->getTimeSlots();
			$timeSlots->rewind();
			//remove all timeslots up to the first available one or until none are left
			while (($timeSlot = $timeSlots->current()) !== FALSE && $timeSlot->getTimestamp() < $firstAvailableTimestamp) {
				$timeSlots->detach($timeSlot);
				$timeSlots->next();
			}
			//everything before the first available timeslot has now been removed

			//get the last timeslot in the entire date slot storage
			$lastTimestamp = $dateSlotStorage->getLast()->getTimeSlots()->getLast()->getTimestamp();

			$dateTime = new DateTime($lastTimestamp);
			//calculate the max days ahead to create dateslots for, by removing the number of days already present in the storage (excluding the last day)
			$maxDaysAhead = ceil((($type->getMaxDaysForward() * 24 * 60 * 60) - ($lastTimestamp - $firstAvailableTimestamp)) / 60 / 60 / 24);
			#@SHOULD bug: if the last timestamp is mid-dateslot, the function doesn't simply add new timeslots to it, but tries to create a new dateslot starting from last timestamp
			$this->createDateSlots($dateSlotStorage,$dateTime,$type,$agenda,$maxDaysAhead);
			//everything missing after the last timeslot has now been added
		} else { //the first available day is not in the storage, so it needs a complete rebuild
			$dateSlotStorage = $this->buildStorageObject($type, $agenda);
		}

		return $dateSlotStorage;
	}

	/**
	 * Calculates DateTime representing the first available time for appointments.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type domain model object instance
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @return DateTime Represents the first available time
	 */
	protected function getFirstAvailableTime(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda) {
		$offsetHours = $type->getBlockedHours();
		$offsetHoursWorkdays = $type->getBlockedHoursWorkdays();
		if ($offsetHours < $offsetHoursWorkdays) { //would be entirely nonsensical while making things unnecessarily harder, so it's corrected here
			$offsetHours = $offsetHoursWorkdays;
		}

		$excludeHolidays = $type->getExcludeHolidays();
		$dateTime = new DateTime();
		$now = $dateTime->getTimestamp();
		$dateTime->modify("+$offsetHours hours"); //this sets the DateTime object at the offset to start finding slots

		// if true, we need to take into account separation of weekend- and workdays, in calculating the correct offset
		if ($offsetHoursWorkdays > 0) {
			$this->recalculateDateTimeForWorkdaysOffset($dateTime, $offsetHoursWorkdays, $now, $excludeHolidays, $agenda->getHolidays());
		}

		return $dateTime;
	}

	/**
	 * Recalculates the DateTime reference in case the WorkdaysOffset-setting produces a different offset the normal Offset-setting.
	 *
	 * @param DateTime $dateTime The DateTime reference that is going to be recalculated
	 * @param integer $offsetHoursWorkdays The offsetHoursWorkdays setting of the appointment Type
	 * @param integer $now Timestamp NOW, not of the DateTime reference
	 * @param boolean $excludeHolidays Whether holidays are to be excluded. If TRUE, holidays aren't workdays either..
	 * @param array $holidayArray Contains the holidays in dd-mm-yyyy format
	 * @return void
	 */
	protected function recalculateDateTimeForWorkdaysOffset(DateTime $dateTime, $offsetHoursWorkdays, $now, $excludeHolidays = FALSE, $holidayArray = array()) {
		$endTimestamp = $dateTime->getTimestamp(); //this will also be our fallback if nothing changes
		$workTimestamp = $dateTime->setTimestamp($now)->modify("+$offsetHoursWorkdays hours")->getTimestamp();
		$dateTime->setTimestamp($now)->setTime(0,0); //note that we set time to 00:00, because we need to move DateTime to the START of days
		$timestamp = $now;

		while ($timestamp < $endTimestamp) {
			$day = intval($dateTime->format('N')); // [1 (Monday) - 7 (Sunday)]
			$date = $dateTime->format('d-m-Y');

			//if a weekday and NOT a holiday if excluded
			if ($day < 6 && (!$excludeHolidays || !in_array($date,$holidayArray))) {
				$add = 6 - $day; //number of days until weekend

				//if holidays are exempt, we'll move up in days until weekend or the first holiday
				if ($excludeHolidays) {
					for ($d = 0; $d < $add; $d++) {
						$date = $dateTime->modify('+1 day')->format('d-m-Y');
						if (in_array($date,$holidayArray)) {
							break; //reached a holiday before weekend
						}
					}
				} else { //no holidays to exclude means we can just skip to the weekend
					$dateTime->modify("+$add day");
				}

				//set the new timestamp while making sure it doesn't exceed the offset
				$timestamp = $dateTime->getTimestamp();
				if ($timestamp > $endTimestamp) {
					$timestamp = $endTimestamp;
				}
			} else { //if weekend OR a holiday when excluded
				//skip to monday or the day after the holiday
				$add = ($day === 6) ? 2 : 1;
				$nextTimestamp = $dateTime->modify("+$add day")->getTimestamp();

				//the time we skipped ahead is the amount of NOT-workday-time we need to take into account for the offset
				$addedTime = $nextTimestamp - $timestamp;
				$timestamp = $nextTimestamp;
				$workTimestamp += $addedTime;
				//if NOT-workday-time exceeded the original offset, turning it into the new offset will make us adhere to the workdays-offset
				if ($endTimestamp < $workTimestamp) {
					$endTimestamp = $workTimestamp;
				}
			}
		}

		$dateTime->setTimestamp($endTimestamp);
	}

	/**
	 * Creates dateslots and adds them to the $dateSlotStorage.
	 *
	 * @param Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_DateSlot> $dateSlotStorage The dateslot storage in which to create the dateslots
	 * @param DateTime $dateTime The DateTime representing the offset for the first available time
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type domain model object instance
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @param integer $maxDaysAhead The amount of days ahead of $dateTime to get dateslots for
	 * @param integer $excludeAppointment UID of an appointment that is ignored
	 * @return void
	 */
	protected function createDateSlots(Tx_Appointments_Persistence_KeyObjectStorage $dateSlotStorage, DateTime $dateTime, Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, $maxDaysAhead = 365, $excludeAppointment = 0) {
		$excludeHolidays = $type->getExcludeHolidays();
		$holidayArray = $agenda->getHolidays();

		for ($counter = 0; $counter < $maxDaysAhead; $counter++) {

			$currentDate = $dateTime->format('d-m-Y'); //note that 'current' from here on is equivalent to 'current in loop'
			if (!$excludeHolidays || !in_array($currentDate,$holidayArray)) {
				$currentDate .= ' 00:00:00'; //from here on, it's used to identify results from findBetween()

				$day = $dateTime->format('l'); //full day name (english)
				$func = 'getMaxAmount'.$day;
				$maxAmount = $type->$func();
				if ($maxAmount > 0) {
					//we don't want $dateTime adjusted, so we make several instances from here on
					$startDateTime = new DateTime($currentDate); #@TODO waarom niet gewoon clone gebruiken voor al die nieuwe instances?
					$endDateTime = new DateTime($currentDate);
					$endDateTime->modify('+1 day');
						//used for interval-logic later on, but convenient to create here due to endDateTime's current state
					$dateTimeEnd = new DateTime($endDateTime->format('d-m-Y H:i:s'));
					$overrideStopTime = $dateTimeEnd->modify('-1 minute')->getTimestamp();

					//if the 'per var' settings have values, override the datetime-reach for appointments to find
					$perVarDays = $type->getPerVarDays();
					if ($perVarDays > 0) {
						$startDateTime->modify('-'.($perVarDays-1).' days');
						$endDateTime->modify('+'.($perVarDays-1).' days');
					}


					//if exclusive availability enabled, only include appointments of this type in the search
					$types = $type->getExclusiveAvailability() ? array($type) : NULL;

					$appointments = $this->appointmentRepository->findBetween($agenda, $startDateTime, $endDateTime, 1, 24, $excludeAppointment,$types);
					$appointmentsCurrent = isset($appointments[$currentDate]) ? $appointments[$currentDate] : array();
					$appointmentAmount = count($appointmentsCurrent);
					if ($appointmentAmount < $maxAmount) {

						$maxAmountPerVarDays = $type->getMaxAmountPerVarDays();
						if ($maxAmountPerVarDays > 0 && $perVarDays > 0) {
							$notAllowed = FALSE;
							if (!$this->processPerVarDays($appointments, $appointmentAmount, $currentDate, new DateTime($startDateTime->format('d-m-Y H:i:s')), new DateTime($endDateTime->format('d-m-Y H:i:s')), $maxAmountPerVarDays)) {
								$notAllowed = TRUE;
							}

							$interval = $type->getPerVarDaysInterval();
							if ($interval > 0) {
								$startDateTime->modify("-$interval hours");
								$endDateTime->modify("+$interval hours");
								$appointments = $this->appointmentRepository->findBetween($agenda, $startDateTime, $endDateTime, 1, $interval, $excludeAppointment,$types);
								if (!$this->processPerVarDaysInterval($appointments, $startDateTime, $endDateTime, $dateTime, $dateTimeEnd, $maxAmountPerVarDays, $perVarDays, $interval)) {
									$notAllowed = TRUE;
								}
								$overrideStopTime = $dateTimeEnd->getTimestamp(); //might have been altered in interval method
							}

							if ($notAllowed) {
								$dateTime->modify('+1 day')->setTime(0,0);
								continue;
							}
						}


						$timestamp = $dateTime->getTimestamp();
						$func = 'getStopTime'.$day;
						$stopTime = $type->$func();
						if (!isset($stopTime[0])) { #@TODO remove these checks as soon as TCA regexp eval is added
							$stopTime = '23:59';
						}
						$stopTimestamp = strtotime($stopTime,$timestamp);
						if ($overrideStopTime < $stopTimestamp) {
							$stopTimestamp = $overrideStopTime;
						}

						if ($timestamp <= $stopTimestamp) {
							$dateSlot = new Tx_Appointments_Domain_Model_DateSlot();
							$dateSlot->setTimestamp($dateTime->getTimestamp());
							$this->createTimeSlots($dateSlot, $type, $stopTimestamp, $appointmentsCurrent);
							//it's possible that an amount of appointments lower than max can leave space for 0 timeSlots
							//so we have to make sure that isn't the case before adding the dateSlot
							if ($dateSlot->getTimeSlots()->count() > 0) {
								$dateSlot->setKey($dateTime->format(self::DATESLOT_KEY_FORMAT));
								$dateSlot->setDayName($dateTime->format('l'));
								$dayShort = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.day_s'.$dateTime->format('N'), $this->extensionName);
								$dateSlot->setLabel($dayShort . ' ' . $dateTime->format('d-m-Y'));
								$dateSlotStorage->attach($dateSlot);
							}
						}
					}
				}
			}

			//sets time to 00:00 because we're already moving to the next day and the original
			//time was only relevant to be between start and stop time on the first candidate-day
			$dateTime->modify('+1 day')->setTime(0,0);
		}
	}

	/**
	 * Processes the standard 'per var days' settings.
	 *
	 * Effectively checks if the current day reaches a max allowed appointments together
	 * with 'per var days' counting backwards or forward.
	 *
	 * @param array $appointments Appointments within 'per var days' range
	 * @param integer $appointmentAmount Amount of appointments of current day
	 * @param string $currentDate String representation of current day date
	 * @param DateTime $startDateTime Represents starting point of the 'before' days
	 * @param DateTime $endDateTime Represents the end point of the 'after' days
	 * @param integer $maxAmountPerVarDays Number of appointments allowed per var days
	 * @return boolean Returns FALSE if current day is excluded from availability because of a reached max amount
	 */
	protected function processPerVarDays($appointments, $appointmentAmount, $currentDate, DateTime $startDateTime, DateTime $endDateTime, $maxAmountPerVarDays) {
		$stats = array(
				'appointmentAmountBackward' => $appointmentAmount,
				'appointmentAmountForward' => $appointmentAmount
		);

		//counts amount of appointments 'per var days' backward and forward, including current day
		$f = $startDateTime->format('d-m-Y H:i:s');
		$e = $currentDate;
		foreach ($stats as &$amount) {
			while ($f !== $e) {
				if (isset($appointments[$f])) {
					$amount += count($appointments[$f]);
				}
				$f = $startDateTime->modify('+1 day')->format('d-m-Y H:i:s');
			}
			//note that we skip current day in the counting of both loops, because the initial $appointmentAmount value already represents it
			$f = $startDateTime->modify('+1 day')->format('d-m-Y H:i:s');
			$e = $endDateTime->format('d-m-Y H:i:s');
		}

		if ($stats['appointmentAmountBackward'] >= $maxAmountPerVarDays || $stats['appointmentAmountForward'] >= $maxAmountPerVarDays) {
			//the per var days max has already been reached
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Processes the 'per var days' settings with regards to its 'interval/buffer' setting.
	 *
	 * Effectively checks if time blocks (buffer) of current day need to be excluded from
	 * timeslot calculation. Appropriately sets $dateTime and $dateTimeEnd to reflect this.
	 *
	 * Note that a return value of TRUE does not guarantee availability, as it does not
	 * check if $dateTimeEnd < $dateTime. (for now)
	 *
	 * @param array $appointments Appointments within 'per var days' range, including the additional buffer
	 * @param DateTime $startDateTime Represents starting point of the 'before' buffer
	 * @param DateTime $endDateTime Represents the end point of the 'after' buffer
	 * @param DateTime $dateTime Current dateTime
	 * @param DateTime $dateTimeEnd Represents the end of current dateTime
	 * @param integer $maxAmountPerVarDays Number of appointments allowed per var days
	 * @param integer $perVarDays Number of var days
	 * @param integer $interval The interval time block size (in hours), or buffer
	 * @return boolean Returns FALSE if there are definitely no available interval time blocks in current day to put appointments in
	 */
	protected function processPerVarDaysInterval($appointments, DateTime $startDateTime, DateTime $endDateTime, DateTime $dateTime, DateTime $dateTimeEnd, $maxAmountPerVarDays, $perVarDays, $interval) {
		//creates an accurate array representation of (un)available interval blocks
		$blockArray = array();
		do {
			$blockArray[$startDateTime->format('d-m-Y H:i:s')] = array();
		} while ($startDateTime->modify("+$interval hours")->getTimestamp() < $endDateTime->getTimestamp());
		$blockArray = array_merge($blockArray,$appointments);

		//separates the interval blocks in current, after and before (reversed)
		$blocksPerDay = intval(24/$interval);
		$currentDayOffset = $perVarDays * $blocksPerDay - ($blocksPerDay-1);
		$blocksCurrent = array_splice($blockArray,$currentDayOffset,$blocksPerDay);
		$blocksAfter = array_splice($blockArray,$currentDayOffset);
		$blocksBeforeReversed = array_reverse($blockArray,1); //reverse 'before', as we need to count from closest to farthest from 'current'

		$stats = array(
				'before' => array(
						'blocks' => $blocksBeforeReversed,
						'appointmentCount' => 0,
						'bufferMultiplier' => 0,
						'modifier' => '+',
						'dateTime' => $dateTime
				),
				'after' => array(
						'blocks' => $blocksAfter,
						'appointmentCount' => 0,
						'bufferMultiplier' => 0,
						'modifier' => '-',
						'dateTime' => $dateTimeEnd
				)
		);

		foreach ($stats as &$stat) {
			//count appoints until the first free block is reached
			foreach ($stat['blocks'] as $block) {
				if (($c = count($block)) === 0) {
					break;
				}
				$stat['appointmentCount'] += $c;
			}

			//find the first free block in 'current' and splice off anything up to that point from the beginning
			while (($b = each($blocksCurrent)) !== FALSE) {
				$stat['bufferMultiplier']++;
				$block = $b['value'];
				if (($c = count($block)) === 0) {
					array_splice($blocksCurrent,0,($stat['bufferMultiplier']-1));
					break;
				}
				$stat['appointmentCount'] += $c;
			}

			//reverse (and rewind) 'current' because the second $stat needs to splice off the end of 'current' instead of the beginning
			$blocksCurrent = array_reverse($blocksCurrent,1);
		}

		//if 'current' has 2 or more interval block that present a disconnection between consecutive 'taken' interval blocks..
		if (count($blocksCurrent) > 1) {
			//find out if the appointments up until the first free interval block reached the max amount
			foreach ($stats as &$stat) {
				if ($stat['appointmentCount'] >= $maxAmountPerVarDays) {
					//adds/subtracts available interval block time from the beginning or end of the current day for use by timeslots
					$stat['dateTime']->modify($stat['modifier'] . ($interval*$stat['bufferMultiplier']) . ' hours');
				}
			}
		} else { //if 'current' has 1 or 0 free interval blocks..
			$totalAppointmentCount = 0;
			//check if placing any more appointments within 'current' would still respect the configured buffer (interval)
			foreach ($stats as $stat) {
				$totalAppointmentCount += $stat['appointmentCount'];
			}
			if ($totalAppointmentCount >= $maxAmountPerVarDays) {
				//would not respect configured buffer, thus disallow
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Initializes the Tx_Appointments_Persistence_KeyObjectStorage properties and builds its content.
	 *
	 * @param Tx_Appointments_Domain_Model_DateSlot $dateSlot DateSlot domain model object instance
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @param integer $endTimestamp The end timestamp
	 * @param Array $appointments Appointments of the day
	 * @return void
	 */
	protected function createTimeSlots(Tx_Appointments_Domain_Model_DateSlot $dateSlot, Tx_Appointments_Domain_Model_Type $type, $endTimestamp, $appointments = array()) {
		$originalTimestamp = $dateSlot->getTimestamp();
		$dateTime = new DateTime();
		$dateTime->setTimestamp($originalTimestamp);
		$day = $dateTime->format('l');
		$func = 'getStartTime'.$day;
		$startTime = $type->$func();
		if (!isset($startTime[0])) {
			$startTime = '00:00';
		}
		$func = 'getMinuteInterval'.$day;
		$intervalMinutes = $type->$func();

		$timestamp = $dateTime->modify($startTime)->getTimestamp();
		$intervalSeconds = $intervalMinutes * 60;

		//makes the hours actually count when appointing time slots
		if ($timestamp < $originalTimestamp) { #@TODO if we want to make it day-based instead of hour-based, how can we do that without affecting originalTimestamp-alterations in previous functions?
			//most of the times, this solution should be faster than a loop-solution
			$diff = $originalTimestamp - $timestamp;
			$intervalIncrease = intval(ceil($diff / $intervalSeconds));
			$timestamp += $intervalIncrease * $intervalSeconds;
		}

		$blockedTime = array(); //this array represents blocks of time in which no timeslots are available
		$desiredReserveBlock = $type->getBetweenMinutes() * 60;
		$defaultDuration = $type->getDefaultDuration() * 60 - 1; //-1 or we lose the final possible timeslot before each appointment
		foreach ($appointments as $appointment) {
			$thisReservedBlock = $appointment->getType()->getBetweenMinutes() * 60;
			$thisEndReserved = $appointment->getEndReserved()->getTimestamp();
			//makes sure there is enough time reserved when another type of appointment requires less than the current
			if ($desiredReserveBlock > $thisReservedBlock) {
				$thisEndReserved += $desiredReserveBlock - $thisReservedBlock;
			}
			$blockedTime[] = array(
				//defaultduration-1 needs to be taken from the begin timestamp, or we still get timeslots that overlap regardless
				'begin' => $appointment->getBeginReserved()->getTimestamp() - $defaultDuration,
				'end' => $thisEndReserved
			);
		}
		//the last block forces correct behaviour of the coming loop with regards to available time, either after the last appointment or lack thereof
		$blockedTime[] = array(
			'begin' => $endTimestamp + 1, //+1 or we lose the final timeslot because in the next while, final $currentEndTimestamp === final $timestamp
			'end' => $endTimestamp
		);
		foreach ($blockedTime as $block) {
			$currentEndTimestamp = $block['begin'];
			while ($timestamp < $currentEndTimestamp) {
				$dateSlot->addTimeSlot($this->createTimeSlot($timestamp));
				$timestamp += $intervalSeconds;
			}
			$timestamp = $block['end'];
		}
	}

	/**
	 * Creates and returns a timeslot object instance.
	 *
	 * @param integer $timestamp The timeslot timestamp
	 * @return Tx_Appointments_Domain_Model_TimeSlot
	 */
	protected function createTimeSlot($timestamp) {
		$timeSlot = new Tx_Appointments_Domain_Model_TimeSlot();
		$timeSlot->setKey(strftime('%Y-%m-%d %H:%M:%S',$timestamp));
		$timeSlot->setTimestamp($timestamp);
		$timeSlot->setLabel(strftime('%H:%M',$timestamp));
		return $timeSlot;
	}



	//*********
	// CACHING
	//*********

	/**
	 * Gets the cache entry
	 *
	 * @param	string		$key		cache key
	 * @param	string		$identifier	unique identifier
	 * @return	string		serialized cache entry
	 * @author	Steffen Kamper <http://buzz.typo3.org/people/steffen-kamper/article/using-cache-in-extensions/>
	 */
	protected function getCache($key, $identifier) {
		$cacheIdentifier = $this->extensionName . '-' . $identifier;
		$cacheHash = md5($cacheIdentifier . $key);
		return t3lib_pageSelect::getHash($cacheHash);
	}

	/**
	 * Stores data in cache
	 *
	 * @param	string		$key		cache key
	 * @param	string		$identifier	unique identifier
	 * @param	array		$data		your data to store in cache
	 * @return	void		...
	 * @author Steffen Kamper <http://buzz.typo3.org/people/steffen-kamper/article/using-cache-in-extensions/>
	 */
	protected function setCache($key, $identifier, $data) {
		$cacheIdentifier = $this->extensionName . '-' . $identifier;
		$cacheHash = md5($cacheIdentifier . $key);
		t3lib_pageSelect::storeHash($cacheHash,serialize($data),$cacheIdentifier);
	}

	/**
	 * Gets a dateslot storage object from cache, or builds it from scratch.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type to which the dateslot storage belongs to
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda to which the dateslot storage belongs to
	 * @return Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_DateSlot>
	 */
	protected function getStorageObject(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda) {
		$data = $this->getStorageObjectFromCache($type, $agenda);
		if ($data === FALSE) {
			//not cached so begin building
			$id = 'dateSlotStorage';
			$data = $this->buildStorageObject($type, $agenda);
			$this->setCache($key,$id,$data);
		}
		return $data;
	}

	/**
	 * Gets a dateslot storage object from cache.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type to which the dateslot storage belongs to
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda to which the dateslot storage belongs to
	 * @return Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_DateSlot>
	 */
	protected function getStorageObjectFromCache(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda) {
		$id = 'dateSlotStorage';
		$minutes = 60; //the number of minutes before a cache entry expires #@TODO make it configurable?
		$timestampPerMinutesVar = ceil( time() / ($minutes * 60) );
		$key = md5($agenda->getUid() . '-' . $type->getUid() . '-' . $timestampPerMinutesVar);

		$cacheContent = $this->getCache($key,$id);

		if (isset($cacheContent)) {
			$data = unserialize($cacheContent);
			//makes sure unserialization delivered a valid object, considering there are (inconsistent) issues with serialized object storages
			if ($data instanceof Tx_Appointments_Persistence_KeyObjectStorage) {
				return $data;
			}
		}

		return FALSE;
	}

	/**
	 * Resets a storageobject by invalidating its cache entry.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type to which the dateslot storage belongs to
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda to which the dateslot storage belongs to
	 * @return void
	 */
	public function resetStorageObject(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda) {
		$typeUid = $type->getUid();

		$id = 'dateSlotStorage';
		$minutes = 60; //the number of minutes before a cache entry expires #@TODO make it configurable?
		$timestampPerMinutesVar = ceil( time() / ($minutes * 60) );
		$key = md5($agenda->getUid() . '-' . $typeUid . '-' . $timestampPerMinutesVar);

		$cacheContent = $this->getCache($key,$id);

		if (isset($cacheContent)) {
			$this->setCache($key,$id,NULL);
		}

		$this->appointmentRepository->persistChanges();
		unset($this->dateSlots[$typeUid]);
	}

}
?>