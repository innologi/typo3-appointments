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
 * FormFieldValue domain model
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Model_FormFieldValue extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * value
	 *
	 * Validated by the FormFieldValueValidator
	 *
	 * @var string
	 */
	protected $value;

	/**
	 * The formfield this value belongs to
	 *
	 * No use in making these lazy, because when formFieldValues are called,
	 * formfields are ALWAYS called as well to put the value in context.
	 *
	 * @var Tx_Appointments_Domain_Model_FormField
	 * @validate NotEmpty
	 */
	protected $formField;

	/**
	 * Returns the value
	 *
	 * @return string $value
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Sets the value
	 *
	 * @param string $value
	 * @return void
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * Returns the formField
	 *
	 * @return Tx_Appointments_Domain_Model_FormField $formField
	 */
	public function getFormField() {
		return $this->formField;
	}

	/**
	 * Sets the formField
	 *
	 * @param Tx_Appointments_Domain_Model_FormField $formField
	 * @return void
	 */
	public function setFormField(Tx_Appointments_Domain_Model_FormField $formField) {
		$this->formField = $formField;
	}

	/**
	 * Returns storage index
	 *
	 * @return string
	 */
	public function getIndex() {
		return $this->index; #@TODO cleanup
	}

}
?>