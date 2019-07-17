<?php
namespace Innologi\Appointments\Domain\Service;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2017 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use Innologi\Appointments\Persistence\KeyObjectStorage;
use Innologi\Appointments\Domain\Model\{Type, Agenda, Appointment, DateSlot, TimeSlot};
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
/**
 * Manages the date- and their time slots, persists them and their changes to cache.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class SlotService implements SingletonInterface {

	//constants
	const DATESLOT_KEY_FORMAT = 'Ymd';
	//if I change these, I should remember to also change the value formats @ templates, or else we get in trouble in at least editAction
	const TIMESLOT_KEY_FORMAT = 'YmdHis';
	const TIMESLOT_KEY_FORMAT_ALT = '%Y%m%d%H%M%S';
	#@LOW once the TYPO3 dependency is raised, I should see if the template values are still required, and if so, if I can reach these constants from within the template

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
	protected $dateSlots = [];

	/**
	 * Single DateSlot storage array
	 *
	 * @var array
	 */
	protected $singleDateSlots = [];

	/**
	 * appointmentRepository
	 *
	 * @var \Innologi\Appointments\Domain\Repository\AppointmentRepository
	 */
	protected $appointmentRepository;

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
	 */
	protected $cache;

	/**
	 *
	 * @param \Innologi\Appointments\Domain\Repository\AppointmentRepository $appointmentRepository
	 * @return void
	 */
	public function injectAppointmentRepository(\Innologi\Appointments\Domain\Repository\AppointmentRepository $appointmentRepository)
	{
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
		$this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('appointments_slots');
	}

	/**
	 * Returns the dateSlot storage of the specified type.
	 *
	 * @param Type $type Appointment Type domain model object instance
	 * @param Agenda $agenda Agenda domain model object instance
	 * @return KeyObjectStorage
	 */
	public function getDateSlots(Type $type, Agenda $agenda) {
		$typeUid = $type->getUid();
		if ($this->clearExpiredAppointmentTimeSlots($agenda) || !isset($this->dateSlots[$typeUid])) {
			$this->dateSlots[$typeUid] = $this->getStorageObject($type, $agenda);
		}

		return $this->dateSlots[$typeUid];
	}

	/**
	 * Get dateslots and include the timeslots in use due to the current appointment
	 *
	 * @param Appointment $appointment Current appointment
	 * @param boolean $disregardConditions If TRUE, disregards the type's firstAvailableTime and maxDaysForward conditions for current appointment
	 * @return KeyObjectStorage
	 */
	public function getDateSlotsIncludingCurrent(Appointment $appointment, $disregardConditions = FALSE) {
		$type = $appointment->getType();
		$agenda = $appointment->getAgenda();

		$dateSlotStorage = $this->getDateSlots($type,$agenda);

		if (!$appointment->_isNew() && $appointment->getCreationProgress() !== Appointment::EXPIRED) {
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
	 * @param Type $type Appointment Type domain model object instance
	 * @param Agenda $agenda Agenda domain model object instance
	 * @param \DateTime $dateTime DateTime object to get Dateslot for
	 * @return KeyObjectStorage
	 */
	public function getSingleDateSlot(Type $type, Agenda $agenda, \DateTime $dateTime) {
		$typeUid = $type->getUid();
		$dateSlotKey = $dateTime->format(self::DATESLOT_KEY_FORMAT);

		if (($cleared = $this->clearExpiredAppointmentTimeSlots($agenda)) || !isset($this->singleDateSlots[$typeUid][$dateSlotKey])) {
			if (
					!$cleared && ( //try to retrieve it from a normal dateSlotStorage if available
							(isset($this->dateSlots[$typeUid][$dateSlotKey]) && $dateSlotStorage = $this->dateSlots[$typeUid])
							|| (
									($dateSlotStorage = $this->getStorageObjectFromCache('dateSlotStorage', $type, $agenda)) !== FALSE
									&& isset($dateSlotStorage[$dateSlotKey])
							)
			)) {
				$this->singleDateSlots[$typeUid][$dateSlotKey] = new KeyObjectStorage();
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
	 * @param Appointment $appointment Appointment that is ignored when building the storage
	 * @param Type $type Appointment Type domain model object instance
	 * @param boolean $disregardConditions If TRUE, disregards the type's firstAvailableTime and maxDaysForward conditions
	 * @return KeyObjectStorage
	 */
	public function getSingleDateSlotIncludingCurrent(Appointment $appointment, Type $type = NULL, $disregardConditions = FALSE) {
		if ($type === NULL) {
			$type = $appointment->getType();
		}
		$agenda = $appointment->getAgenda();

		$this->clearExpiredAppointmentTimeSlots($agenda); #@LOW probably inefficient @ every type with limitTypes :/
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
	 * @param \DateTime $dateTime DateTime to get dateslot for
	 * @return KeyObjectStorage
	 */
	public function getDummyDateSlot(\DateTime $dateTime) {
		$dateSlotStorage = new KeyObjectStorage();
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
	 * @param KeyObjectStorage $dateSlotStorage Contains date slots
	 * @param Appointment $appointment Appointment to get the dateslot for
	 * @return boolean|KeyObjectStorage
	 */
	public function getTimeSlots(KeyObjectStorage $dateSlotStorage, Appointment $appointment) {
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
	 * @param Appointment $appointment The appointment to check the timeslot for
	 * @return boolean
	 */
	public function isTimeSlotAllowed(Appointment $appointment) {
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
	 * @param Agenda $agenda Agenda domain model object instance
	 * @return boolean TRUE on change, FALSE on no change
	 */
	protected function clearExpiredAppointmentTimeSlots(Agenda $agenda) {
		$temp = $this->appointmentRepository->findExpiredUnfinished($agenda, $this->expireMinutes);

		if (!empty($temp)) {
			$types = new ObjectStorage();
			foreach ($temp as $appointment) {
				$appointment->setCreationProgress(Appointment::EXPIRED);
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
	 * Initializes the KeyObjectStorage properties and builds its content.
	 *
	 * @param Type $type Appointment Type domain model object instance
	 * @param Agenda $agenda Agenda domain model object instance
	 * @param Appointment $excludeAppointment Appointment that is ignored when building the storage
	 * @return KeyObjectStorage<\Innologi\Appointments\Domain\Model\DateSlot>
	 */
	protected function buildStorageObject(Type $type, Agenda $agenda, Appointment $excludeAppointment = NULL) {
		$dateSlotStorage = new KeyObjectStorage();

		$dateTime = $this->getFirstAvailableTime($type, $agenda);

		$maxDaysAhead = $type->getMaxDaysForward(); #@TODO __add an override maxdaysforward in agenda, that, if set, works on all types in the plugin?
		$this->createDateSlots($dateSlotStorage, $dateTime, $type, $agenda, $maxDaysAhead, $excludeAppointment);

		return $dateSlotStorage;
	}

	/**
	 * Initializes the KeyObjectStorage properties and builds its content for a SINGLE date slot.
	 *
	 * NOTE THAT $dateTime IS ADJUSTED, so be sure whether you need a reference or clone
	 *
	 * @param Type $type Type domain model object instance
	 * @param Agenda $agenda Agenda domain model object instance
	 * @param \DateTime $dateTime Represents the date for which to retrieve the dateslot and time from which to retrieve the timeslots
	 * @param Appointment $excludeAppointment Appointment that is ignored when building the storage
	 * @param boolean $disregardConditions If TRUE, disregards the type's firstAvailableTime and maxDaysForward conditions
	 * @return KeyObjectStorage
	 */
	protected function buildSingleStorageObject(Type $type, Agenda $agenda, \DateTime $dateTime, Appointment $excludeAppointment = NULL, $disregardConditions = FALSE) {
		$dateSlotStorage = new KeyObjectStorage();

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
	 * @param Type $type Appointment Type domain model object instance
	 * @param Agenda $agenda Agenda domain model object instance
	 * @return KeyObjectStorage<\Innologi\Appointments\Domain\Model\DateSlot>
	 */
	protected function alterStorageObject(Type $type, Agenda $agenda) { #@TODO test this one
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
			$dateTime = new \DateTime();
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
	 * @param Type $type Appointment Type domain model object instance
	 * @param Agenda $agenda Agenda domain model object instance
	 * @return \DateTime Represents the first available time
	 */
	protected function getFirstAvailableTime(Type $type, Agenda $agenda) {
		$offsetHours = $type->getBlockedHours();
		$offsetHoursWorkdays = $type->getBlockedHoursWorkdays();
		if ($offsetHours < $offsetHoursWorkdays) { //would be entirely nonsensical while making things unnecessarily harder, so it's corrected here
			$offsetHours = $offsetHoursWorkdays;
		}

		$excludeHolidays = $type->getExcludeHolidays();
		$dateTime = new \DateTime();
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
	 * @param \DateTime $dateTime The DateTime reference that is going to be recalculated
	 * @param integer $offsetHoursWorkdays The offsetHoursWorkdays setting of the appointment Type
	 * @param integer $now Timestamp NOW, not of the DateTime reference
	 * @param boolean $excludeHolidays Whether holidays are to be excluded. If TRUE, holidays aren't workdays either..
	 * @param array $holidayArray Contains the holidays in dd-mm-yyyy format as keys
	 * @return void
	 */
	protected function recalculateDateTimeForWorkdaysOffset(\DateTime $dateTime, $offsetHoursWorkdays, $now, $excludeHolidays = FALSE, $holidayArray = array()) {
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
	 * @param KeyObjectStorage $dateSlotStorage The dateslot storage in which to create the dateslots
	 * @param \DateTime $dateTime The DateTime representing the offset for the first available time
	 * @param Type $type Appointment Type domain model object instance
	 * @param Agenda $agenda Agenda domain model object instance
	 * @param integer $maxDaysAhead The amount of days ahead of $dateTime to get dateslots for
	 * @param Appointment $excludeAppointment Appointment that is ignored
	 * @return void
	 */
	protected function createDateSlots(KeyObjectStorage $dateSlotStorage, \DateTime $dateTime, Type $type, Agenda $agenda, $maxDaysAhead = 365, Appointment $excludeAppointment = NULL) {
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
					$startDateTime = new \DateTime($currentDate); //don't clone the first, because $dateTime might have a different time #@LOW does that really matter?
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

					$appointments = $this->appointmentRepository->findBetween($agenda, $startDateTime, $endDateTime, $types, 0, $excludeAppointment, 1, FALSE);
					$appointmentsTotalAmount = count($appointments);
					$appointments = $this->appointmentRepository->rearrangeAppointmentArray($appointments);
					// @TODO this non-restricting stuff probably isn't necessary for appointments of exclusive availability
					$appointmentsNonRestricting = $this->appointmentRepository->findBetween($agenda, $startDateTime, $endDateTime, $types, 0, $excludeAppointment, 1, TRUE);
					$appointmentsNonRestricting = $this->appointmentRepository->rearrangeAppointmentArray($appointmentsNonRestricting);
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
									$appointments = $this->appointmentRepository->findBetween($agenda, $startDateTime, $endDateTime, $types, 0, $excludeAppointment, 1, FALSE);
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
						if (!isset($stopTime[0])) { #@LOW remove these checks as soon as TCA regexp eval is added
							$stopTime = '23:59';
						}
						$stopTimestamp = strtotime($stopTime,$timestamp);
						if ($overrideStopTime < $stopTimestamp) {
							$stopTimestamp = $overrideStopTime;
						}

						if ($timestamp <= $stopTimestamp) {
							if (isset($appointmentsNonRestricting[$currentDate])) {
								$appointmentsCurrent = $appointmentsCurrent + $appointmentsNonRestricting[$currentDate];
								ksort($appointmentsCurrent);
							}

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
	 * @param \DateTime $dateTime The DateTime object representing the date
	 * @return DateSlot
	 */
	protected function createDateSlot(\DateTime $dateTime) {
		$dateSlot = new DateSlot();
		$dateSlot->setTimestamp($dateTime->getTimestamp());
		$dateSlot->setKey($dateTime->format(self::DATESLOT_KEY_FORMAT));
		$dateSlot->setDayName($dateTime->format('l'));
		$dayShort = LocalizationUtility::translate('tx_appointments_list.day_s'.$dateTime->format('N'), $this->extensionName);
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
	 * @param \DateTime $startDateTime Represents starting point of the 'before' days
	 * @param \DateTime $endDateTime Represents the end point of the 'after' days
	 * @param integer $maxAmountPerVarDays Number of appointments allowed per var days
	 * @return boolean Returns FALSE if current day is excluded from availability because of a reached max amount
	 */
	protected function processPerVarDays($appointments, $appointmentAmount, $currentDate, \DateTime $startDateTime, \DateTime $endDateTime, $maxAmountPerVarDays) {
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
	 * @param \DateTime $startDateTime Represents starting point of the 'before' buffer
	 * @param \DateTime $endDateTime Represents the end point of the 'after' buffer
	 * @param \DateTime $dateTime Current dateTime
	 * @param \DateTime $dateTimeEnd Represents the end of current dateTime
	 * @param integer $maxAmountPerVarDays Number of appointments allowed per var days
	 * @param integer $perVarDays Number of var days
	 * @param integer $interval The interval time block size (in hours), or buffer
	 * @return boolean Returns FALSE if there are definitely no available interval time blocks in current day to put appointments in
	 */
	protected function processPerVarDaysInterval($appointments, \DateTime $startDateTime, \DateTime $endDateTime, \DateTime $dateTime, \DateTime $dateTimeEnd, $maxAmountPerVarDays, $perVarDays, $interval) {
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
			foreach ($blocksCurrent as $block) {
				$stats[$k]['bufferMultiplier']++; //[BUG]: the bug causes us to not write to a reference anymore, but that causes $stat['bufferMultiplier'] not to update here..
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
	 * Initializes the \Innologi\Appointments\Persistence\KeyObjectStorage properties and builds its content.
	 *
	 * @param DateSlot $dateSlot DateSlot domain model object instance
	 * @param Agenda $agenda Agenda domain model object instance
	 * @param integer $endTimestamp The end timestamp
	 * @param array $appointments Appointments of the day
	 * @return void
	 */
	protected function createTimeSlots(DateSlot $dateSlot, Type $type, $endTimestamp, $appointments = array()) {
		$originalTimestamp = $dateSlot->getTimestamp();
		$dateTime = new \DateTime();
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

		// if dateslot-key is ever based on timeslot
		// we can't use this as long as dateFirst functionality works with 00:00:00 timestamps
		#$firstTimeSlot = $dateSlot->getTimeSlots()->getFirst();
		#if ($firstTimeSlot !== NULL) {
		#	$dateSlot->setKey($firstTimeSlot->getKey());
		#}
	}

	/**
	 * Creates and returns a timeslot object instance.
	 *
	 * @param integer $timestamp The timeslot timestamp
	 * @return TimeSlot
	 */
	protected function createTimeSlot($timestamp) {
		$timeSlot = new TimeSlot();
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
	 * @param Type $type
	 * @param Agenda $agenda
	 * @param string $id
	 * @return mixed
	 */
	protected function getCache(Type $type, Agenda $agenda, string $id) {
		$data = NULL;
		$entryIdentifier = $this->generateCacheEntryIdentifier($type, $agenda, $id);
		if ($this->cache->has($entryIdentifier)) {
			$data = $this->cache->get($entryIdentifier);
		}
		return $data;
	}

	/**
	 * Stores data in cache
	 *
	 * @param Type $type
	 * @param Agenda $agenda
	 * @param string $id
	 * @param mixed $data
	 * @return	void
	 */
	protected function setCache(Type $type, Agenda $agenda, string $id, $data) {
		$this->cache->set(
			$this->generateCacheEntryIdentifier($type, $agenda, $id),
			$data,
			[
				'type_' . $type->getUid()
			]
		);
	}

	/**
	 * Gets the cache entry identifier
	 *
	 * @param Type $type
	 * @param Agenda $agenda
	 * @param string $id
	 * @return string
	 */
	protected function generateCacheEntryIdentifier(Type $type, Agenda $agenda, string $id): string {
		return md5($agenda->getUid() . '-' . $type->getUid() . '-' . $id);
	}

	/**
	 * Gets a dateslot storage object from cache, or builds it from scratch.
	 *
	 * @param Type $type Appointment Type to which the dateslot storage belongs to
	 * @param Agenda $agenda The agenda to which the dateslot storage belongs to
	 * @return KeyObjectStorage
	 */
	protected function getStorageObject(Type $type, Agenda $agenda) {
		$id = 'dateSlotStorage';
		$data = $this->getStorageObjectFromCache($id, $type, $agenda);
		if ($data === FALSE) {
			//not cached so begin building
			$data = $this->buildStorageObject($type, $agenda);
			$this->setCache($type, $agenda, $id, $data);
		}
		return $data;
	}

	/**
	 * Gets a single dateslot storage object from cache, or build it from scratch.
	 *
	 * NOTE THAT buildSingleStorageObject() ADJUSTS $dateTime!
	 *
	 * @param Type $type Appointment Type to which the dateslot storage belongs to
	 * @param Agenda $agenda The agenda to which the dateslot storage belongs to
	 * @param \DateTime $dateTime The DateTime of the storage object
	 * @return KeyObjectStorage
	 */
	protected function getSingleStorageObject(Type $type, Agenda $agenda, \DateTime $dateTime) {
		$dateSlotKey = $dateTime->format(self::DATESLOT_KEY_FORMAT);

		$id = 'singleDateSlotStorage';
		$data = $this->getStorageObjectFromCache($id, $type, $agenda);
		//note that the singleStorageObject is stored per type/agenda in its entirety in a single cache record
		if (($data === FALSE && $data = array()) || !isset($data[$dateSlotKey])) {
			//not cached so begin building
			$data[$dateSlotKey] = $this->buildSingleStorageObject($type, $agenda, $dateTime);
			$this->setCache($type, $agenda, $id, $data);
		}
		return $data[$dateSlotKey];
	}

	/**
	 * Gets a dateslot storage object from cache.
	 *
	 * @param string $key cache key
	 * @param string $id cache identifier
	 * @param Type $type Appointment Type to which the dateslot storage belongs to
	 * @param Agenda $agenda The agenda to which the dateslot storage belongs to
	 * @return KeyObjectStorage
	 */
	protected function getStorageObjectFromCache($id, Type $type, Agenda $agenda) {
		$data = $this->getCache($type, $agenda, $id);
		if ($data !== NULL && ($data instanceof KeyObjectStorage || gettype($data) === 'array')) {
			return $data;
		}
		return FALSE;
	}

	/**
	 * Resets a storageobject by invalidating its cache entry.
	 *
	 * @param Type $type Appointment Type to which the dateslot storage belongs to
	 * @param Agenda $agenda The agenda to which the dateslot storage belongs to
	 * @return void
	 */
	public function resetStorageObject(Type $type, Agenda $agenda) {
		$tags = [];
		//if the current type is exclusive, we only need to reset that one
		$isExclusive = $type->getExclusiveAvailability() && $type->getDontBlockTypes();
		$types = $isExclusive ? [$type] : $agenda->getTypes();
		foreach ($types as $type) {
			//if the current type wasn't exclusive, only reset those that aren't exclusive either
			if ($isExclusive || !$type->getExclusiveAvailability() ) {
				$uid = $type->getUid();
				$tags[] = 'type_' . $uid;
				unset($this->dateSlots[$uid]);
				unset($this->singleDateSlots[$uid]);
			}
		}
		$this->cache->flushByTags($tags);
	}


	#@LOW imagine a different approach to building and caching storageObjects:
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