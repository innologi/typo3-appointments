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
 * DateSlot
 *
 * A non-persisted entity for easy use in views.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Model_DateSlot extends Tx_Appointments_Domain_Model_TimeSlot {

	/**
	 * Time Slots
	 *
	 * @var Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_TimeSlot>
	 * @lazy
	 */
	protected $timeSlots; #@SHOULD @lazy doesn't make sense since it's not persisted, but does it hurt to leave it there in case I ever persist these?

	/**
	 * Day name
	 *
	 * @var string
	 */
	protected $dayName;

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
		$this->timeSlots = new Tx_Appointments_Persistence_KeyObjectStorage();
	}

	/**
	 * Adds a TimeSlot
	 *
	 * @param Tx_Appointments_Domain_Model_TimeSlot $timeSlot
	 * @return void
	 */
	public function addTimeSlot(Tx_Appointments_Domain_Model_TimeSlot $timeSlot) {
		$this->timeSlots->attach($timeSlot);
	}

	/**
	 * Removes a TimeSlot
	 *
	 * @param Tx_Appointments_Domain_Model_TimeSlot $timeSlotToRemove The TimeSlot to be removed
	 * @return void
	 */
	public function removeTimeSlot(Tx_Appointments_Domain_Model_TimeSlot $timeSlotToRemove) {
		$this->timeSlots->detach($timeSlotToRemove);
	}

	/**
	 * Returns the timeSlots
	 *
	 * @return Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_TimeSlot> $timeSlots
	 */
	public function getTimeSlots() {
		return $this->timeSlots;
	}

	/**
	 * Sets the timeSlots
	 *
	 * @param Tx_Appointments_Persistence_KeyObjectStorage<Tx_Appointments_Domain_Model_TimeSlot> $timeSlot
	 * @return void
	 */
	public function setTimeSlots(Tx_Appointments_Persistence_KeyObjectStorage $timeSlots) {
		$this->timeSlots = $timeSlots;
	}

	/**
	 * Sets the day name
	 *
	 * @param string $dayName
	 * @return void
	 */
	public function setDayName($dayName) {
		$this->dayName = $dayName;
	}

	/**
	 * Returns the day name
	 *
	 * @return string $dayName
	 */
	public function getDayName() {
		return $this->dayName;
	}

}
?>