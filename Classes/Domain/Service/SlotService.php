<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
	//if I change these, I should remember to also change the value formats @ templates, or else we get in trouble in at least editAction
	const TIMESLOT_KEY_FORMAT = 'YmdHis';
	const TIMESLOT_KEY_FORMAT_ALT = '%Y%m%d%H%M%S';
	#@SHOULD once the TYPO3 dependency is raised, I should see if the template values are still required, and if so, if I can reach these constants from within the template

	/**
	 * Extension name
	 *
	 * @var string
	 */
	protected $extensionName;

	/**
	 * Reservation time for timeslots
	 *
	 * @var integer
	 */
	protected $expireMinutes;

	/**
	 * Sets whether timeslots will be shifted on a daily basis (FALSE) or per appointment-type interval (TRUE)
	 *
	 * @var boolean
	 */
	protected $intervalBasedShifting = FALSE;

	/**
	 * DateSlot storage array
	 *
	 * @var array
	 */
	protected $dateSlots = array();

	/**
	 * Single DateSlot storage array
	 *
	 * @var array
	 */
	protected $singleDateSlots = array();

	/**
	 * appointmentRepository
	 *
	 * @var Tx_Appointments_Domain_Repository_AppointmentRepository
	 */
	protected $appointmentRepository;

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
	 * Initializes slot service
	 *
	 * @param string $extensionName
	 * @param integer $expireMinutes Minutes after which a reserved timeslot expires
	 * @param boolean $intervalBasedShifting If TRUE, enables shifting of timeslots per appointment type interval instead of a daily basis
	 * @return void
	 */
	public function initialize($extensionName, $expireMinutes, $intervalBasedShifting) {
		$this->extensionName = $extensionName;
		$this->expireMinutes = $expireMinutes;
		$this->intervalBasedShifting = $intervalBasedShifting;
	}

	/**
	 * Returns the dateSlot storage of the specified type.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type domain model object instance
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @return Tx_Appointments_Persistence_KeyObjectStorage
	 */
	public function getDateSlots(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda) {
		$typeUid = $type->getUid();
		if ($this->clearExpiredAppointmentTimeSlots($agenda) || !isset($this->dateSlots[$typeUid])) {
			$this->dateSlots[$typeUid] = $this->getStorageObject($type, $agenda);
		}

		return $this->dateSlots[$typeUid];
	}

	/**
	 * Get dateslots and include the timeslots in use due to the current appointment
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment Current appointment
	 * @param boolean $disregardConditions If TRUE, disregards the type's firstAvailableTime and maxDaysForward conditions for current appointment
	 * @return Tx_Appointments_Persistence_KeyObjectStorage
	 */
	public function getDateSlotsIncludingCurrent(Tx_Appointments_Domain_Model_Appointment $appointment, $disregardConditions = FALSE) {
		$type = $appointment->getType();
		$agenda = $appointment->getAgenda();

		$dateSlotStorage = $this->getDateSlots($type,$agenda);

		if (!$appointment->_isNew() && $appointment->getCreationProgress() !== Tx_Appointments_Domain_Model_Appointment::EXPIRED) {
			$singleDateSlotStorage = $this->getSingleDateSlotIncludingCurrent($appointment,$type,$disregardConditions);
			$dateSlotStorage->addAll($singleDateSlotStorage);
		}

		return $dateSlotStorage;
	}

	/**
	 * Returns a dateSlotStorage with a single dateSlot based on timestamp.
	 *
	 * NOTE THAT getSingleStorageObject() ADJUSTS $dateTime!
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type domain model object instance
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @param DateTime $dateTime DateTime object to get Dateslot for
	 * @return Tx_Appointments_Persistence_KeyObjectStorage
	 */
	public function getSingleDateSlot(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, DateTime $dateTime) {
		$typeUid = $type->getUid();
		$dateSlotKey = $dateTime->format(self::DATESLOT_KEY_FORMAT);

		if (($cleared = $this->clearExpiredAppointmentTimeSlots($agenda)) || !isset($this->singleDateSlots[$typeUid][$dateSlotKey])) {
			if (
					!$cleared && ( //try to retrieve it from a normal dateSlotStorage if available
							(isset($this->dateSlots[$typeUid][$dateSlotKey]) && $dateSlotStorage = $this->dateSlots[$typeUid])
							|| (
									($dateSlotStorage = $this->getStorageObjectFromCache($this->getCacheKey($type, $agenda),'dateSlotStorage',$type, $agenda)) !== FALSE
									&& isset($dateSlotStorage[$dateSlotKey])
							)
			)) {
				$this->singleDateSlots[$typeUid][$dateSlotKey] = new Tx_Appointments_Persistence_KeyObjectStorage();
				$this->singleDateSlots[$typeUid][$dateSlotKey]->attach($dateSlotStorage[$dateSlotKey]);
			} else {
				$this->singleDateSlots[$typeUid][$dateSlotKey] = $this->getSingleStorageObject($type,$agenda,$dateTime);
			}
		}

		return $this->singleDateSlots[$typeUid][$dateSlotKey];
	}

	/**
	 * Returns a dateSlotStorage with a single dateSlot, including the times that would be allowed without current appointment.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment Appointment that is ignored when building the storage
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type domain model object instance
	 * @param boolean $disregardConditions If TRUE, disregards the type's firstAvailableTime and maxDaysForward conditions
	 * @return Tx_Appointments_Persistence_KeyObjectStorage
	 */
	public function getSingleDateSlotIncludingCurrent(Tx_Appointments_Domain_Model_Appointment $appointment, Tx_Appointments_Domain_Model_Type $type = NULL, $disregardConditions = FALSE) {
		if ($type === NULL) {
			$type = $appointment->getType();
		}
		$agenda = $appointment->getAgenda();

		$this->clearExpiredAppointmentTimeSlots($agenda); #@SHOULD probably inefficient @ every type with limitTypes :/
		$dateSlotStorage = $this->buildSingleStorageObject($type,$agenda,clone $appointment->getBeginTime(),$appointment,$disregardConditions);

		#@TODO finish this alternative that could alter a cached dateSlotStorage instead
		//clone, because we don't want to influence the storage mapped in the service
		#$dateSlotStorage = clone $this->getSingleDateSlot($type, $agenda, clone $appointment->getBeginTime());
		#$dateSlotKey = $appointment->getBeginTime()->format(self::DATESLOT_KEY_FORMAT);
		#if (isset($dateSlotStorage[$dateSlotKey])) {
		#	$dateSlot = $dateSlotStorage[$dateSlotKey];
		#	$this->alterDateSlot($dateSlot,$appointment);
		#}

		return $dateSlotStorage;
	}

	/**
	 * Gets a dummy dateslotstorage. For cosmetic purposes, e.g. a permanently disabled selectbox.
	 *
	 * @param DateTime $dateTime DateTime to get dateslot for
	 * @return Tx_Appointments_Persistence_KeyObjectStorage
	 */
	public function getDummyDateSlot(DateTime $dateTime) {
		$dateSlotStorage = new Tx_Appointments_Persistence_KeyObjectStorage();
		$dateSlot = $this->createDateSlot($dateTime);
		$dateSlot->addTimeSlot($this->createTimeSlot($dateTime->getTimestamp()));
		$dateSlotStorage->attach($dateSlot);
		return $dateSlotStorage;
	}

	/**
	 * Gets timeslots for the day of the appointment, from the date slot storage.
	 * If the dateslot is not available for the appointment, will return FALSE,
	 * unless the appointment is unfinished.
	 *
	 * @param Tx_Appointments_Persistence_KeyObjectStorage $dateSlotStorage Contains date slots
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment Appointment to get the dateslot for
	 * @return boolean|Tx_Appointments_Persistence_KeyObjectStorage
	 */
	public function getTimeSlots(Tx_Appointments_Persistence_KeyObjectStorage $dateSlotStorage, Tx_Appointments_Domain_Model_Appointment $appointment) {
		$date = $appointment->getBeginTime();
		$key = $date->format(self::DATESLOT_KEY_FORMAT);

		if (!isset($dateSlotStorage[$key])) {
			return FALSE;
		}

		$dateSlot = $dateSlotStorage[$key];
		return $dateSlot->getTimeSlots();
	}

	/**
	 * Checks if the timeslot for the appointment is allowed. If the timeslot wasn't possible to begin with,
	 * will return FALSE.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to check the timeslot for
	 * @return boolean
	 */
	public function isTimeSlotAllowed(Tx_Appointments_Domain_Model_Appointment $appointment) {
		$dateSlotStorage = $this->getSingleDateSlotIncludingCurrent($appointment);
		$timeSlots = $this->getTimeSlots($dateSlotStorage, $appointment);
		if ($timeSlots) {
			$key = $appointment->getBeginTime()->format(self::TIMESLOT_KEY_FORMAT);
			if (isset($timeSlots[$key])) {
				return $timeSlots[$key];
			}
		}
		return FALSE;
	}

	/**
	 * Clears expired appointments to free up timeslots, and returns whether there are any changes.
	 *
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @return boolean TRUE on change, FALSE on no change
	 */
	protected function clearExpiredAppointmentTimeSlots(Tx_Appointments_Domain_Model_Agenda $agenda) {
		$temp = $this->appointmentRepository->findExpiredUnfinished($agenda, $this->expireMinutes);

		if (!empty($temp)) {
			$types = new Tx_Extbase_Persistence_ObjectStorage();
			foreach ($temp as $appointment) {
				$appointment->setCreationProgress(Tx_Appointments_Domain_Model_Appointment::EXPIRED);
				//this is really the only reason we have a boolean in update()'s arguments: prevent multiple resets for a single type
				$this->appointmentRepository->update($appointment,FALSE);
				#$types->attach($appointment->getType()); //this makes sure we only reset each type's storageObject once, so that we don't make redundant cache queries
			}
			$this->appointmentRepository->persistChanges(); //persist the changed appointments because persistAll() isn't up until after rebuilding slotStorages
			#foreach ($types as $type) { //reset after persist is always better, because this way nothing gets rebuild in another request before persisting
				#$this->resetStorageObject($type,$agenda);
			#}
			$this->resetStorageObject($appointment->getType(),$agenda);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Initializes the Tx_Appointments_Persistence_KeyObjectStorage properties and builds its content.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type domain model object instance
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @param Tx_Appointments_Domain_Model_Appointment $excludeAppointment Appointment that is ignored when building the storage
	 * @return Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_DateSlot>
	 */
	protected function buildStorageObject(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, Tx_Appointments_Domain_Model_Appointment $excludeAppointment = NULL) {
		$dateSlotStorage = new Tx_Appointments_Persistence_KeyObjectStorage();

		$dateTime = $this->getFirstAvailableTime($type, $agenda);

		$maxDaysAhead = $type->getMaxDaysForward(); #@TODO __add an override maxdaysforward in agenda, that, if set, works on all types in the plugin?
		$this->createDateSlots($dateSlotStorage, $dateTime, $type, $agenda, $maxDaysAhead, $excludeAppointment);

		return $dateSlotStorage;
	}

	/**
	 * Initializes the Tx_Appointments_Persistence_KeyObjectStorage properties and builds its content for a SINGLE date slot.
	 *
	 * NOTE THAT $dateTime IS ADJUSTED, so be sure whether you need a reference or clone
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Type domain model object instance
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda Agenda domain model object instance
	 * @param DateTime $dateTime Represents the date for which to retrieve the dateslot and time from which to retrieve the timeslots
	 * @param Tx_Appointments_Domain_Model_Appointment $excludeAppointment Appointment that is ignored when building the storage
	 * @param boolean $disregardConditions If TRUE, disregards the type's firstAvailableTime and maxDaysForward conditions
	 * @return Tx_Appointments_Persistence_KeyObjectStorage
	 */
	protected function buildSingleStorageObject(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, DateTime $dateTime, Tx_Appointments_Domain_Model_Appointment $excludeAppointment = NULL, $disregardConditions = FALSE) {
		$dateSlotStorage = new Tx_Appointments_Persistence_KeyObjectStorage();

		$dateTime->setTime(0,0); //we want to get the entire dateslot, so just in case

		if (!$disregardConditions) {
			//prepare condition checks
			$typeDateTime = $this->getFirstAvailableTime($type, $agenda);
			$firstAvailableTime = $typeDateTime->getTimestamp();
			$maxDaysForward = $type->getMaxDaysForward() - 1; //-1 because the first available day is included
			$lastAvailableTime = $typeDateTime->modify('+'.$maxDaysForward.' days')->getTimestamp();
			$beginTime = $dateTime->getTimestamp();
			$endDateTime = clone $dateTime;
			$endTime = $endDateTime->modify('+1 day')->getTimestamp();
		}
		if (
				$disregardConditions || (
					$lastAvailableTime >= $beginTime
					&& $firstAvailableTime < $endTime
					&& (	//if firstAvailableTime isn't AFTER beginTime, we have to set beginTime to firstAvailableTime
							$firstAvailableTime <= $beginTime
							|| $dateTime->setTimestamp($firstAvailableTime)
					)
				) //checks if the dateTime takes place between the first and last available timestamps
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
	protected function alterStorageObject(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda) { #@FIXME test this one
		$typeUid = $type->getUid();
		$dateSlotStorage = $this->dateSlots[$typeUid];
		$firstAvailableTimestamp = $this->getFirstAvailableTime($type, $agenda)->getTimestamp();
		$dateSlotStorage->rewind();
		//remove all dateslots up to the first available one or until none are left (dateslots contain a timestamp of their first minute)
		while (($dateSlot = $dateSlotStorage->current()) !== FALSE && $dateSlot->getTimestamp() < $firstAvailableTimestamp) {
			$dateSlotStorage->detach($dateSlot);
			$dateSlotStorage->next();
		}
		if ($dateSlot !== FALSE && $dateSlotStorage->count() > 1) { //the first available day is already present in the storage and not the only one
			$timeSlots = $dateSlot->getTimeSlots();
			$timeSlots->rewind();
			//remove all timeslots up to the first available one or until none are left
			while (($timeSlot = $timeSlots->current()) !== FALSE && $timeSlot->getTimestamp() < $firstAvailableTimestamp) {
				$timeSlots->detach($timeSlot);
				$timeSlots->next();
			}
			if ($timeSlot->count() === 0) {
				$dateSlotStorage->detach($dateSlot);
			}
			//everything before the first available timeslot has now been removed

			//remove the last dateslot as well as it should have changed, and set dateTime to its timestamp
			$lastDateSlot = $dateSlotStorage->getLast();
			$dateSlotStorage->detach($lastDateSlot); //hence the count > 1: if it was the only one, we could have done a rebuild
			$lastTimestamp = $lastDateSlot->getTimestamp();
			$dateTime = new DateTime();
			$dateTime->setTimestamp($lastTimestamp);

			//calculate the max days ahead to create dateslots for, by removing the number of days already present in the storage (excluding the last day)
			$maxDaysAhead = ceil((($type->getMaxDaysForward() * 24 * 3600) - ($lastTimestamp - $firstAvailableTimestamp)) / 3600 / 24);

			//everything missing after the last timeslot now gets added
			$this->createDateSlots($dateSlotStorage,$dateTime,$type,$agenda,$maxDaysAhead);
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
		if (!$this->intervalBasedShifting) { //if intervalbased shifting isn't enabled, just move starting point to midnight
			$dateTime->modify('+1 day')->setTime(0,0); #@TODO _make the time configurable?
		}
		$now = $dateTime->getTimestamp();
		$dateTime->modify("+$offsetHours hours"); //this sets the DateTime object at the offset to start finding slots

		// if true, we need to take into account separation of weekend- and workdays, in calculating the correct offset
		if ($offsetHoursWorkdays > 0) {
			$this->recalculateDateTimeForWorkdaysOffset($dateTime, $offsetHoursWorkdays, $now, $excludeHolidays, $agenda->getHolidayArray());
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
	 * @param array $holidayArray Contains the holidays in dd-mm-yyyy format as keys
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
			if ($day < 6 && (!$excludeHolidays || !isset($holidayArray[$date]))) {
				$add = 6 - $day; //number of days until weekend

				//if holidays are exempt, we'll move up in days until weekend or the first holiday
				if ($excludeHolidays) {
					for ($d = 0; $d < $add; $d++) {
						$date = $dateTime->modify('+1 day')->format('d-m-Y');
						if (isset($holidayArray[$date])) {
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
	 * @param Tx_Appointments_Domain_Model_Appointment $excludeAppointment Appointment that is ignored
	 * @return void
	 */
	protected function createDateSlots(Tx_Appointments_Persistence_KeyObjectStorage $dateSlotStorage, DateTime $dateTime, Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, $maxDaysAhead = 365, Tx_Appointments_Domain_Model_Appointment $excludeAppointment = NULL) {
		$excludeHolidays = $type->getExcludeHolidays();
		$holidayArray = $agenda->getHolidayArray();

		for ($counter = 0; $counter < $maxDaysAhead; $counter++) {

			$currentDate = $dateTime->format('d-m-Y'); //note that 'current' from here on is equivalent to 'current in loop'
			if (!$excludeHolidays || !isset($holidayArray[$currentDate])) {
				$currentDate .= ' 00:00:00'; //from here on, it's used to identify results from findBetween()

				$day = $dateTime->format('l'); //full day name (english)
				$func = 'getMaxAmount'.$day;
				$maxAmount = $type->$func();
				if ($maxAmount > 0) {
					//we don't want $dateTime adjusted, so we clone several instances from here on
					$startDateTime = new DateTime($currentDate); //don't clone the first, because $dateTime might have a different time #@SHOULD does that really matter?
					$endDateTime = clone $startDateTime;
					$endDateTime->modify('+1 day');
						//used for interval-logic later on, but convenient to create here due to endDateTime's current state
					$dateTimeEnd = clone $endDateTime;
					$overrideStopTime = $dateTimeEnd->modify('-1 minute')->getTimestamp();

					//if the 'per var' settings have values, override the datetime-reach for appointments to find
					$perVarDays = $type->getPerVarDays();
					if ($perVarDays > 0) {
						$startDateTime->modify('-'.($perVarDays-1).' days');
						$endDateTime->modify('+'.($perVarDays-1).' days');
					}

					//if exclusive availability enabled, only include appointments of this type in the search
					$types = $type->getExclusiveAvailability() ? array($type) : NULL;

					$appointments = $this->appointmentRepository->findBetween($agenda, $startDateTime, $endDateTime, $types, $excludeAppointment, 1);
					$appointmentsTotalAmount = count($appointments);
					$appointments = $this->appointmentRepository->rearrangeAppointmentArray($appointments, 24);
					$appointmentsCurrent = isset($appointments[$currentDate]) ? $appointments[$currentDate] : array();
					$appointmentsCurrentAmount = count($appointmentsCurrent);
					if ($appointmentsCurrentAmount < $maxAmount) {

						$maxAmountPerVarDays = $type->getMaxAmountPerVarDays();
						if ($maxAmountPerVarDays > 0 && $perVarDays > 0 && $appointmentsTotalAmount > 0) { //if totalAmount is 0, the entire perVarDays mechanism is unnecessary
							$notAllowed = FALSE;
							if ($appointmentsTotalAmount >= $maxAmountPerVarDays //the totalAmount needs to be at least as much as the maxAmount for perVarDays to have any effect
									&& !$this->processPerVarDays($appointments, $appointmentsCurrentAmount, $currentDate, clone $startDateTime, clone $endDateTime, $maxAmountPerVarDays)
							) {
								$notAllowed = TRUE;
							} else {
								$interval = $type->getPerVarDaysInterval();
								if ($interval > 0) {
									$startDateTime->modify("-$interval hours");
									$endDateTime->modify("+$interval hours");
									$appointments = $this->appointmentRepository->findBetween($agenda, $startDateTime, $endDateTime, $types, $excludeAppointment, 1);
									$appointmentsTotalAmount = count($appointments);
									$appointments = $this->appointmentRepository->rearrangeAppointmentArray($appointments, $interval);
									if ($appointmentsTotalAmount >= $maxAmountPerVarDays //the totalAmount needs to be at least as much as the maxAmount for perVarDaysInterval to have any effect
											&& !$this->processPerVarDaysInterval($appointments, $startDateTime, $endDateTime, $dateTime, $dateTimeEnd, $maxAmountPerVarDays, $perVarDays, $interval)
									) {
										$notAllowed = TRUE;
									}
									$overrideStopTime = $dateTimeEnd->getTimestamp(); //might have been altered in interval method
								}
							}

							if ($notAllowed) {
								$dateTime->modify('+1 day')->setTime(0,0);
								continue;
							}
						}


						$timestamp = $dateTime->getTimestamp();
						$func = 'getStopTime'.$day;
						$stopTime = $type->$func();
						if (!isset($stopTime[0])) { #@SHOULD remove these checks as soon as TCA regexp eval is added
							$stopTime = '23:59';
						}
						$stopTimestamp = strtotime($stopTime,$timestamp);
						if ($overrideStopTime < $stopTimestamp) {
							$stopTimestamp = $overrideStopTime;
						}

						if ($timestamp <= $stopTimestamp) {
							$dateSlot = $this->createDateSlot($dateTime);
							$this->createTimeSlots($dateSlot, $type, $stopTimestamp, $appointmentsCurrent);
							//it's possible that an amount of appointments lower than max can leave space for 0 timeSlots
							//so we have to make sure that isn't the case before adding the dateSlot
							if ($dateSlot->getTimeSlots()->count() > 0) {
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
	 * Creates a single date slot, without timeslots.
	 *
	 * @param DateTime $dateTime The DateTime object representing the date
	 * @return Tx_Appointments_Domain_Model_DateSlot
	 */
	protected function createDateSlot(DateTime $dateTime) {
		$dateSlot = new Tx_Appointments_Domain_Model_DateSlot();
		$dateSlot->setTimestamp($dateTime->getTimestamp());
		$dateSlot->setKey($dateTime->format(self::DATESLOT_KEY_FORMAT));
		$dateSlot->setDayName($dateTime->format('l'));
		$dayShort = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.day_s'.$dateTime->format('N'), $this->extensionName);
		$dateSlot->setLabel($dayShort . ' ' . $dateTime->format('d-m-Y'));
		return $dateSlot;
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
		foreach ($stats as $k=>$amount) { //replaced the reference because of the PHP bug documented in processPerVarDaysInterval()
			while ($f !== $e) {
				if (isset($appointments[$f])) {
					$stats[$k] += count($appointments[$f]);
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
	 * Note that this function suffered from a PHP bug. See the 4 [BUG] inline comments below.
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

		foreach ($stats as $k=>$stat) {
			//count appoints until the first free block is reached
			foreach ($stat['blocks'] as $block) {
				if (($c = count($block)) === 0) {
					break;
				}
				//[BUG]: there is a PHP bug that sets $stats[after][appointmentCount] = $stats[before][appointmentCount] later on, if using reference &$stat instead here
				$stats[$k]['appointmentCount'] += $c;
			}

			//find the first free block in 'current' and splice off anything up to that point from the beginning
			while (($b = each($blocksCurrent)) !== FALSE) {
				$stats[$k]['bufferMultiplier']++; //[BUG]: the bug causes us to not write to a reference anymore, but that causes $stat['bufferMultiplier'] not to update here..
				$block = $b['value'];
				if (($c = count($block)) === 0) {
					array_splice($blocksCurrent,0,($stats[$k]['bufferMultiplier']-1)); //[BUG]: .. so we can't use $stat['bufferMultiplier'] here! only use $stats[$k]['bufferMultiplier']!
					break;
				}
				$stats[$k]['appointmentCount'] += $c;
			}

			//reverse (and rewind) 'current' because the second $stat needs to splice off the end of 'current' instead of the beginning
			$blocksCurrent = array_reverse($blocksCurrent,1);
		}

		//if 'current' has 2 or more interval block that present a disconnection between consecutive 'taken' interval blocks..
		if (count($blocksCurrent) > 1) {
			//find out if the appointments up until the first free interval block reached the max amount
			foreach ($stats as $k=>$stat) {
				if ($stat['appointmentCount'] >= $maxAmountPerVarDays) {
					//adds/subtracts available interval block time from the beginning or end of the current day for use by timeslots
					$stats[$k]['dateTime']->modify($stat['modifier'] . ($interval*$stat['bufferMultiplier']) . ' hours');
				}
			}
		} else { //if 'current' has 1 or 0 free interval blocks..
			$totalAppointmentCount = 0;
			//check if placing any more appointments within 'current' would still respect the configured buffer (interval)
			foreach ($stats as $stat) { //[BUG]: PHP [5.3.8] bug triggered here, if earlier &$stat instead of $stats[$k]
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

		#$timestamp = $dateTime->modify($startTime)->getTimestamp(); //absolute times only supported on PHP >= 5.3.6
		$parts = explode(':',$startTime);
		$timestamp = $dateTime->setTime(intval($parts[0]),intval($parts[1]))->getTimestamp();
		$intervalSeconds = $intervalMinutes * 60;

		//makes the hours actually count when appointing time slots
		if ($timestamp < $originalTimestamp) {
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
				'end' => $thisEndReserved < $timestamp ? $timestamp : $thisEndReserved //if timestamp is higher, we have to set it here so the last foreach doesn't make timestamp lower
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
		$timeSlot->setKey(strftime(self::TIMESLOT_KEY_FORMAT_ALT,$timestamp));
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
	 * Gets the cache key of the type and agenda
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda
	 * @param integer $minutes Block of minutes this cache-key is valid for
	 * @return string
	 */
	protected function getCacheKey(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, $minutes = 60) {
		$timestampPerMinutesVar = ceil( time() / ($minutes * 60) );
		return md5($agenda->getUid() . '-' . $type->getUid() . '-' . $timestampPerMinutesVar);
	}

	/**
	 * Gets a dateslot storage object from cache, or builds it from scratch.
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type to which the dateslot storage belongs to
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda to which the dateslot storage belongs to
	 * @return Tx_Appointments_Persistence_KeyObjectStorage
	 */
	protected function getStorageObject(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda) {
		$id = 'dateSlotStorage';
		$key = $this->getCacheKey($type, $agenda); #@SHOULD utilize configurable cache-key minutes??
		$data = $this->getStorageObjectFromCache($key, $id, $type, $agenda);
		if ($data === FALSE) {
			//not cached so begin building
			$data = $this->buildStorageObject($type, $agenda);
			$this->setCache($key,$id,$data);
		}
		return $data;
	}

	/**
	 * Gets a single dateslot storage object from cache, or build it from scratch.
	 *
	 * NOTE THAT buildSingleStorageObject() ADJUSTS $dateTime!
	 *
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type to which the dateslot storage belongs to
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda to which the dateslot storage belongs to
	 * @param DateTime $dateTime The DateTime of the storage object
	 * @return Tx_Appointments_Persistence_KeyObjectStorage
	 */
	protected function getSingleStorageObject(Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda, DateTime $dateTime) {
		$dateSlotKey = $dateTime->format(self::DATESLOT_KEY_FORMAT);

		$id = 'singleDateSlotStorage';
		$key = $this->getCacheKey($type, $agenda);
		$data = $this->getStorageObjectFromCache($key, $id, $type, $agenda);
		//note that the singleStorageObject is stored per type/agenda in its entirety in a single cache record
		if (($data === FALSE && $data = array()) || !isset($data[$dateSlotKey])) {
			//not cached so begin building
			$data[$dateSlotKey] = $this->buildSingleStorageObject($type, $agenda, $dateTime);
			$this->setCache($key,$id,$data);
		}
		return $data[$dateSlotKey];
	}

	/**
	 * Gets a dateslot storage object from cache.
	 *
	 * @param string $key cache key
	 * @param string $id cache identifier
	 * @param Tx_Appointments_Domain_Model_Type $type Appointment Type to which the dateslot storage belongs to
	 * @param Tx_Appointments_Domain_Model_Agenda $agenda The agenda to which the dateslot storage belongs to
	 * @return Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_DateSlot>
	 */
	protected function getStorageObjectFromCache($key, $id, Tx_Appointments_Domain_Model_Type $type, Tx_Appointments_Domain_Model_Agenda $agenda) {
		$cacheContent = $this->getCache($key,$id);
		if (isset($cacheContent)) {
			$data = unserialize($cacheContent);
			//makes sure unserialization delivered a valid object, considering there are (inconsistent) issues with serialized object storages
			if ($data instanceof Tx_Appointments_Persistence_KeyObjectStorage || gettype($data) === 'array') {
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
		$types = $agenda->getTypes();
		foreach ($types as $type) { #@FIXME _currently also resets those with exclusive availability, those could be an exception here, which proves the $type arg useful again
			$typeUid = $type->getUid();
			$key = $this->getCacheKey($type, $agenda);

			$id = 'dateSlotStorage';
			$cacheContent = $this->getCache($key,$id);
			if (isset($cacheContent)) {
				$this->setCache($key,$id,NULL);
			}
			$id = 'singleDateSlotStorage';
			$cacheContent = $this->getCache($key,$id);
			if (isset($cacheContent)) {
				$this->setCache($key,$id,NULL);
			}

			unset($this->dateSlots[$typeUid]);
			unset($this->singleDateSlots[$typeUid]);
		}

		#@SHOULD imagine a different approach to building and caching storageObjects:
		/*
		 * - create a week worth of slots for a type, and cache it with an identifier that changes only when the type record is changed
		 * - retrieve the cache, then create a new storage with it, according to firstAvailableTime and maxDaysForward
		 * - remove all dateslots that are holidays, and place appointments in a blockarray by which we first check max,
		 * then maxpervardays, then maxpervardaysinterval, in turn removing dateslots and timeslots where necessary
		 * - in case timeslots are removed instead of dateslots, check at the end of that part whether there are timeslots
		 * left, and delete the dateslot if not
		 * - cache the result by type, agenda, and a configurable amount of minutes, like now
		 * - etc.
		 *
		 *  Would that be more efficient?
		 *
		 *  I should seriously time the results on empty agenda's, as well as agenda's with small,
		 *  medium or large appointment-sets, iterating with different values for maxDaysForward as well.
		 */
	}

}
?>