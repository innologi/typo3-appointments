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
 * Abstract Agenda Data Container
 *
 * A non-persisted entity for easy use in views.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
abstract class Tx_Appointments_Domain_Model_Agenda_AbstractContainer extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Year
	 *
	 * @var string
	 */
	protected $year;

	/**
	 * back modifier
	 *
	 * @var integer
	 */
	protected $backModifier;

	/**
	 * forward modifier
	 *
	 * @var integer
	 */
	protected $forwardModifier;

	/**
	 * Maximum back modifier
	 *
	 * @var integer
	 */
	protected $maxBack;

	/**
	 * Maximum forward modifier
	 *
	 * @var integer
	 */
	protected $maxForward;

	/**
	 * Array of week storages
	 *
	 * @var Array<Tx_Extbase_Persistence_ObjectStorage>
	 */
	protected $weeks; #@LOW why not array<array> again?

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		$this->weeks = array();
	}

	/**
	 * Returns the name
	 *
	 * @return string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the year
	 *
	 * @return string $year
	 */
	public function getYear() {
		return $this->year;
	}

	/**
	 * Sets the year
	 *
	 * @param string $year
	 * @return void
	 */
	public function setYear($year) {
		$this->year = $year;
	}

	/**
	 * Returns the back modifier
	 *
	 * @return integer $backModifier
	 */
	public function getBackModifier() {
		return $this->backModifier;
	}

	/**
	 * Sets the back modifier
	 *
	 * @param integer $backModifier
	 * @return void
	 */
	public function setBackModifier($backModifier) {
		$this->backModifier = $backModifier;
	}

	/**
	 * Returns the forward modifier
	 *
	 * @return integer $forwardModifier
	 */
	public function getForwardModifier() {
		return $this->forwardModifier;
	}

	/**
	 * Sets the forward modifier
	 *
	 * @param integer $forwardModifier
	 * @return void
	 */
	public function setForwardModifier($forwardModifier) {
		$this->forwardModifier = $forwardModifier;
	}

	/**
	 * Returns maximum back modifier
	 *
	 * @return integer $maxMonthBack
	 */
	public function getMaxBack() {
		return $this->maxBack;
	}

	/**
	 * Sets the maximum back modifier
	 *
	 * @param integer $maxBack
	 * @return void
	 */
	public function setMaxBack($maxBack) {
		$this->maxBack = $maxBack;
	}

	/**
	 * Returns the maximum forward modifier
	 *
	 * @return integer $maxForward
	 */
	public function getMaxForward() {
		return $this->maxForward;
	}

	/**
	 * Sets the maximum forward modifier
	 *
	 * @param integer $maxForward
	 * @return void
	 */
	public function setMaxForward($maxForward) {
		$this->maxForward = $maxForward;
	}

	/**
	 * Returns whether back is allowed
	 *
	 * @return boolean
	 */
	public function getCanBack() {
		return $this->maxBack <= $this->backModifier;
	}

	/**
	 * Returns whether forward is allowed
	 *
	 * @return boolean
	 */
	public function getCanForward() {
		return $this->maxForward >= $this->forwardModifier;
	}

	/**
	 * Adds a week storage
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage $week
	 * @return void
	 */
	public function addWeek(Tx_Extbase_Persistence_ObjectStorage $week) {
		$this->weeks[] = $week;
	}

	/**
	 * Returns the weeks
	 *
	 * @return Array<Tx_Extbase_Persistence_ObjectStorage> $weeks
	 */
	public function getWeeks() {
		return $this->weeks;
	}

	/**
	 * Sets the weeks
	 *
	 * @param Array<Tx_Extbase_Persistence_ObjectStorage> $weeks
	 * @return void
	 */
	public function setWeeks($weeks) {
		$this->weeks = $weeks;
	}

}
?>