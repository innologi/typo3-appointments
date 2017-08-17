<?php
namespace Innologi\Appointments\Domain\Model;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
/**
 * Abstract slot
 *
 * A non-persisted entity for easy use in views.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
abstract class AbstractSlot extends AbstractEntity {

	/**
	 * key
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * label
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * Timestamp
	 *
	 * @var integer
	 */
	protected $timestamp;

	/**
	 * Returns the Key
	 *
	 * @return string $key
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Sets the Key
	 *
	 * @param string $key
	 * @return void
	 */
	public function setKey($key) {
		$this->key = $key;
	}

	/**
	 * Returns the Label
	 *
	 * @return string $label
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * Sets the Label
	 *
	 * @param string $label
	 * @return void
	 */
	public function setLabel($label) {
		$this->label = $label;
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

}