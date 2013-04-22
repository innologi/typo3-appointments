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
 * FormField domain model
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Model_FormField extends Tx_Extbase_DomainObject_AbstractEntity {

	//constants
	const VALIDATE_NOT_EMPTY = 1;
	const VALIDATE_INTEGER = 2;
	const VALIDATE_STRING = 3;
	const VALIDATE_TEXT = 4;
	const VALIDATE_ALPHANUMERIC = 5;
	const VALIDATE_DATE_TIME = 6;
	const VALIDATE_EMAIL_ADDRESS = 7;
	const VALIDATE_FLOAT = 8;
	const VALIDATE_NUMBER = 9;
	const FUNCTION_INFORMATIONAL = 1;
	const FUNCTION_ENABLEFIELD = 2;
	const FUNCTION_ADDTIME = 3;
	const TYPE_BOOLEAN = 1;
	const TYPE_SELECT = 2;
	const TYPE_TEXTSMALL = 3;
	const TYPE_TEXTLARGE = 4;

	/**
	 * Field title
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $title;

	/**
	 * Field label
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $label;

	/**
	 * Context Sensitive Help
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $csh;

	/**
	 * Validation Types
	 *
	 * @var string
	 */
	protected $validationTypes;

	/**
	 * Is date field?
	 *
	 * @var boolean
	 * @transient
	 */
	protected $isDate;

	/**
	 * Field fieldType: boolean, selection, textsmall or textlarge
	 *
	 * @var integer
	 * @validate NotEmpty
	 */
	protected $fieldType;

	/**
	 * Selection choices relevant for boolean and selection types
	 *
	 * @var string
	 */
	protected $choices;

	/**
	 * Selection choices relevant for boolean and selection types
	 * formatted as array
	 *
	 * @var array
	 * @transient
	 */
	protected $choicesForSelect = NULL;

	/**
	 * Field function: informational, enables other field, add time
	 *
	 * @var integer
	 */
	protected $function;

	/**
	 * The amount of time to add
	 *
	 * @var string
	 */
	protected $timeAdd;

	/**
	 * The field this one enables
	 *
	 * @var Tx_Appointments_Domain_Model_FormField
	 * @lazy
	 */
	protected $enableField;

	/**
	 * Sorting priority in its type
	 *
	 * @var integer
	 */
	protected $sorting;

	/**
	 * Returns the title
	 *
	 * @return string $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Returns the label
	 *
	 * @return string $label
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * Sets the label
	 *
	 * @param string $label
	 * @return void
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	/**
	 * Returns the CSH
	 *
	 * @return string $csh
	 */
	public function getCsh() {
		return $this->csh;
	}

	/**
	 * Sets the CSH
	 *
	 * @param string $csh
	 * @return void
	 */
	public function setCsh($csh) {
		$this->csh = $csh;
	}

	/**
	 * Returns the Validation Types
	 *
	 * @return string $validationTypes
	 */
	public function getValidationTypes() {
		return $this->validationTypes;
	}

	/**
	 * Sets the validation types
	 *
	 * @param string $validationTypes
	 * @return void
	 */
	public function setValidationTypes($validationTypes) {
		$this->validationTypes = $validationTypes;
	}

	/**
	 * Returns whether the field is a date.
	 *
	 * @return boolean
	 */
	public function getIsDate() {
		if ($this->isDate === NULL) {
			$this->setIsDate($this->validationTypes);
		}
		return $this->isDate;
	}

	/**
	 * Sets whether the field is a date.
	 *
	 * @param string $validationTypes
	 * @return void
	 */
	protected function setIsDate($validationTypes) {
		$array = explode(',',$validationTypes);
		$this->isDate = in_array(strval(self::VALIDATE_DATE_TIME),$array,1);
	}

	/**
	 * Returns the fieldType
	 *
	 * @return integer $fieldType
	 */
	public function getFieldType() {
		return $this->fieldType;
	}

	/**
	 * Sets the fieldType
	 *
	 * @param integer $fieldType
	 * @return void
	 */
	public function setFieldType($fieldType) {
		$this->fieldType = $fieldType;
	}

	/**
	 * Returns the choices
	 *
	 * @return array $choices
	 */
	public function getChoices() {
		return $this->choices;
	}

	/**
	 * Sets the choices
	 *
	 * Also sets choicesForSelect
	 *
	 * @param string $choices
	 * @return void
	 */
	public function setChoices($choices) {
		$this->choices = $choices;
	}

	/**
	 * Returns choices formatted for select
	 *
	 * @return array
	 */
	public function getChoicesForSelect() {
		if ($this->choicesForSelect === NULL) {
			$this->setChoicesForSelect($this->choices);
		}
		return $this->choicesForSelect;
	}

	/**
	 * Sets choices for form select
	 *
	 * Formats the choices in an array usable for our view purposes
	 *
	 * @param string $choices
	 * @return void
	 */
	protected function setChoicesForSelect($choices) {
		$choices = str_replace("\r\n","\n",$choices);
		$keyArray = array();
		$valueArray = t3lib_div::trimExplode("\n", $choices, 1);
		foreach ($valueArray as $key=>&$choice) {
			if (strpos($choice,'|') === FALSE) {
				$keyArray[$key] = $choice;
			} else {
				$parts = explode('|',$choice,2);
				$keyArray[$key] = $parts[0];
				$choice = $parts[1];
			}
		}
		$valueArray = array_combine($keyArray,$valueArray); //makes keys and values same, which simplifies the view
		$this->choicesForSelect = $valueArray;
	}

	/**
	 * Returns the function
	 *
	 * @return integer $function
	 */
	public function getFunction() {
		return $this->function;
	}

	/**
	 * Sets the function
	 *
	 * @param integer $function
	 * @return void
	 */
	public function setFunction($function) {
		$this->function = $function;
	}

	/**
	 * Returns the timeAdd
	 *
	 * @return string $timeAdd
	 */
	public function getTimeAdd() {
		return $this->timeAdd;
	}

	/**
	 * Sets the timeAdd
	 *
	 * @param string $timeAdd
	 * @return void
	 */
	public function setTimeAdd($timeAdd) {
		$this->timeAdd = $timeAdd;
	}

	/**
	 * Returns the enableField
	 *
	 * @return Tx_Appointments_Domain_Model_FormField $enableField
	 */
	public function getEnableField() { #@TODO really, really REALLY reconsider this one please
		return $this->enableField;
	}

	/**
	 * Sets the enableField
	 *
	 * @param Tx_Appointments_Domain_Model_FormField $enableField
	 * @return void
	 */
	public function setEnableField(Tx_Appointments_Domain_Model_FormField $enableField) {
		$this->enableField = $enableField;
	}

	/**
	 * Returns the sorting
	 *
	 * @return integer $sorting
	 */
	public function getSorting() {
		return $this->sorting;
	}

	/**
	 * Sets the sorting
	 *
	 * @param integer $sorting
	 * @return void
	 */
	public function setSorting($sorting) {
		$this->sorting = $sorting;
	}

}
?>