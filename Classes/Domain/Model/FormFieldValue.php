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
	 * Deleted
	 *
	 * @var boolean
	 * @copy ignore
	 */
	protected $deleted;

	/**
	 * value
	 *
	 * @var string
	 * @validate Tx_Appointments_Domain_Validator_VariableValidator(validationTypes=$formField::validationTypes)
	 * @copy clone
	 */
	protected $value;

	/**
	 * The formfield this value belongs to
	 *
	 * @var Tx_Appointments_Domain_Model_FormField
	 * @validate NotEmpty
	 * @copy reference
	 */
	protected $formField;

	/**
	 * Temporary record / not finalized
	 *
	 * @var boolean
	 * @copy clone
	 */
	protected $temporary = TRUE;

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
	 * Returns the temporary flag
	 *
	 * @return boolean $temporary
	 */
	public function getTemporary() {
		return $this->temporary;
	}

	/**
	 * Sets the temporary flag
	 *
	 * @param boolean $temporary
	 * @return void
	 */
	public function setTemporary($temporary) {
		$this->temporary = $temporary;
	}

	/**
	 * Returns the deleted flag
	 *
	 * @return boolean $deleted
	 */
	public function getDeleted() {
		return $this->deleted;
	}

	/**
	* Sets the deleted flag
	*
	* @param boolean $deleted
	* @return void
	*/
	public function setDeleted($deleted) {
		$this->deleted = $deleted;
	}

}
?>