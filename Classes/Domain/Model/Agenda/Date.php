<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 * Agenda Date
 *
 * A non-persisted entity for easy use in views.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Model_Agenda_Date extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * Classes for agenda use
	 *
	 * @var string
	 */
	protected $agendaClasses = '';

	/**
	 * Number of day in month
	 *
	 * @var string
	 */
	protected $dayNumber;

	/**
	 * Short name of month
	 *
	 * @var string
	 */
	protected $monthShort;

	/**
	 * Timestamp
	 *
	 * @var integer
	 */
	protected $timestamp;

	/**
	 * Is a holiday
	 *
	 * @var boolean
	 */
	protected $isHoliday = FALSE;

	/**
	 * Is today
	 *
	 * @var boolean
	 */
	protected $isToday = FALSE;

	/**
	 * Allows creation of new appointments
	 *
	 * @var boolean
	 */
	protected $allowCreate = FALSE;

	/**
	 * Appointments
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_Appointment>
	 */
	protected $appointments;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
	}

	/**
	 * Initializes all Tx_Extbase_Persistence_ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		/**
		 * Do not modify this method!
		 * It will be rewritten on each save in the extension builder
		 * You may modify the constructor of this class instead
		 */
		$this->appointments = new Tx_Extbase_Persistence_ObjectStorage();
	}

	/**
	 * Returns the Agenda Classes
	 *
	 * @return string $agendaClasses
	 */
	public function getAgendaClasses() {
		return $this->agendaClasses;
	}

	/**
	 * Sets the Agenda Classes
	 *
	 * @param string $agendaClasses
	 * @return void
	 */
	public function setAgendaClasses($agendaClasses) {
		$this->agendaClasses = $agendaClasses;
	}

	/**
	 * Adds a single Agenda Class
	 *
	 * @param string $agendaClass
	 * @return void
	 */
	public function addAgendaClass($agendaClass) {
		$this->agendaClasses .= ' ' . $agendaClass;
	}

	/**
	 * Returns the Day Number
	 *
	 * @return string $dayNumber
	 */
	public function getDayNumber() {
		return $this->dayNumber;
	}

	/**
	 * Sets the Day Number
	 *
	 * @param string $dayNumber
	 * @return void
	 */
	public function setDayNumber($dayNumber) {
		$this->dayNumber = $dayNumber;
	}

	/**
	 * Returns the month short
	 *
	 * @return string $monthShort
	 */
	public function getMonthShort() {
		return $this->monthShort;
	}

	/**
	 * Sets the month short
	 *
	 * @param string $monthShort
	 * @return void
	 */
	public function setMonthShort($monthShort) {
		$this->monthShort = $monthShort;
	}

	/**
	 * Returns the timestamp
	 *
	 * @return integer $timestamp
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * Sets the timestamp
	 *
	 * @param integer $timestamp
	 * @return void
	 */
	public function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
	}

	/**
	 * Returns the Is Holiday
	 *
	 * @return boolean $isHoliday
	 */
	public function getIsHoliday() {
		return $this->isHoliday;
	}

	/**
	 * Sets the Is Holiday
	 *
	 * @param boolean $isHoliday
	 * @return void
	 */
	public function setIsHoliday($isHoliday) {
		$this->isHoliday = $isHoliday;
		if ($isHoliday) {
			$this->addAgendaClass('holiday');
		}
	}

	/**
	 * Returns IsToday
	 *
	 * @return boolean $isToday
	 */
	public function getIsToday() {
		return $this->isToday;
	}

	/**
	 * Sets IsToday
	 *
	 * @param boolean $isToday
	 * @return void
	 */
	public function setIsToday($isToday) {
		$this->isToday = $isToday;
		if ($isToday) {
			$this->addAgendaClass('current');
		}
	}

	/**
	 * Returns allowCreate
	 *
	 * @return boolean $allowCreate
	 */
	public function getAllowCreate() {
		return $this->allowCreate;
	}

	/**
	 * Sets allowCreate
	 *
	 * @param boolean $allowCreate
	 * @return void
	 */
	public function setAllowCreate($allowCreate) {
		$this->allowCreate = $allowCreate;
	}

	/**
	 * Adds an appointment
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment
	 * @return void
	 */
	public function addAppointment(Tx_Appointments_Domain_Model_Appointment $appointment) {
		$this->appointments->attach($appointment);
	}

	/**
	 * Removes an appointment
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointmentToRemove The Appointment to be removed
	 * @return void
	 */
	public function removeAppointment(Tx_Appointments_Domain_Model_Appointment $appointmentToRemove) {
		$this->appointments->detach($appointmentToRemove);
	}

	/**
	 * Returns the appointments
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_Appointment> $appointments
	 */
	public function getAppointments() {
		return $this->appointments;
	}

	/**
	 * Sets the appointments
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Appointments_Domain_Model_Appointment> $appointments
	 * @return void
	 */
	public function setAppointments(Tx_Extbase_Persistence_ObjectStorage $appointments) {
		$this->appointments = $appointments;
	}

}
?>