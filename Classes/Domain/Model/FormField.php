<?php
namespace Innologi\Appointments\Domain\Model;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * FormField domain model
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FormField extends AbstractEntity {

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
	const VALIDATE_NATURALNUMBER = 10;
	const FUNCTION_INFORMATIONAL = 1;
	const FUNCTION_EMAIL = 2;
	const FUNCTION_ADDTIME = 3;
	const TYPE_BOOLEAN = 1;
	const TYPE_SELECT = 2;
	const TYPE_TEXTSMALL = 3;
	const TYPE_TEXTLARGE = 4;
	const TYPE_RADIO = 5;

	/**
	 * Field title
	 *
	 * @var string
	 * @extensionScannerIgnoreLine
	 * @validate NotEmpty
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 */
	protected $title;

	/**
	 * Field label
	 *
	 * @var string
	 * @extensionScannerIgnoreLine
	 * @validate NotEmpty
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 */
	protected $label = '';

	/**
	 * Context Sensitive Help
	 *
	 * @var string
	 */
	protected $csh = '';

	/**
	 * Validation Types
	 *
	 * @var string
	 */
	protected $validationTypes;

	/**
	 * @var array
	 * @extensionScannerIgnoreLine
	 * @transient
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
	 */
	protected $validationTypesArray;

	/**
	 * Is date field?
	 *
	 * @var boolean
	 * @extensionScannerIgnoreLine
	 * @transient
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
	 */
	protected $isDate;

	/**
	 * Is time related?
	 *
	 * @var boolean
	 * @extensionScannerIgnoreLine
	 * @transient
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
	 */
	protected $isTimeRelated;

	/**
	 * Field fieldType: boolean, selection, textsmall or textlarge
	 *
	 * @var integer
	 * @extensionScannerIgnoreLine
	 * @validate NotEmpty
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 */
	protected $fieldType;

	/**
	 * Selection choices relevant for boolean and selection types
	 *
	 * @var string
	 */
	protected $choices = '';

	/**
	 * Selection choices relevant for boolean and selection types
	 * formatted as array
	 *
	 * @var array
	 * @extensionScannerIgnoreLine
	 * @transient
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
	 */
	protected $choicesArray = NULL;

	/**
	 * Field function: informational, enables other field, add time
	 *
	 * @var integer
	 */
	protected $function;

	/**
	 * The field this one enables
	 * -- no longer lazy, because then TYPO3v9 doesn't reliably access its uid in Fluid (unless debugging)
	 *
	 * @var \Innologi\Appointments\Domain\Model\FormField
	 */
	protected $enableField;

	/**
	 * The enableField value that enables this field
	 *
	 * @var string
	 */
	protected $enableValue;

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
	 * Returns validation types as array
	 *
	 * @return array
	 */
	public function getValidationTypesArray() {
		if ($this->validationTypesArray === NULL) {
			$valueArray = GeneralUtility::trimExplode(',', $this->validationTypes, TRUE);
			$this->validationTypesArray = array_combine($valueArray, $valueArray);
		}
		return $this->validationTypesArray;
	}

	/**
	 * Returns whether the field is a date.
	 *
	 * @return boolean
	 */
	public function getIsDate() {
		if ($this->isDate === NULL) {
			$this->setIsDate();
		}
		return $this->isDate;
	}

	/**
	 * Sets whether the field is a date.
	 *
	 * @return void
	 */
	protected function setIsDate() {
		$array = array_flip(explode(',',$this->validationTypes));
		$this->isDate = isset($array[self::VALIDATE_DATE_TIME]);
	}

	/**
	 * Returns whether the field adds time.
	 *
	 * @return boolean
	 */
	public function getIsTimeRelated() {
		return $this->function === self::FUNCTION_ADDTIME;
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
	 * Also sets choicesArray
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
	public function getChoicesArray() {
		if ($this->choicesArray === NULL) {
			$this->setChoicesArray($this->choices);
		}
		return $this->choicesArray;
	}

	/**
	 * Sets choices for form select
	 *
	 * Formats the choices in an array usable for our view purposes
	 *
	 * @param string $choices
	 * @return void
	 */
	protected function setChoicesArray($choices) {
		$choices = str_replace("\r\n","\n",$choices);
		$keyArray = array();
		$valueArray = GeneralUtility::trimExplode("\n", $choices, 1);
		foreach ($valueArray as $key=>&$choice) {
			if (strpos($choice,'|') === FALSE) {
				$keyArray[$key] = $choice;
			} else {
				$parts = explode('|',$choice,2);
				$keyArray[$key] = $parts[0];
				$choice = $parts[1];
			}
		}
		// combines keys and values, simplifying the view
		$valueArray = array_combine($keyArray,$valueArray);
		$this->choicesArray = $valueArray;
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
	 * Returns the enableField
	 *
	 * @return \Innologi\Appointments\Domain\Model\FormField
	 */
	public function getEnableField() {
		return $this->enableField;
	}

	/**
	 * Sets the enableField
	 *
	 * @param \Innologi\Appointments\Domain\Model\FormField $enableField
	 * @return void
	 */
	public function setEnableField(FormField $enableField) {
		$this->enableField = $enableField;
	}

	/**
	 * Returns the enableFieldValue
	 *
	 * @return string $enableValue
	 */
	public function getEnableValue() {
		return $this->enableValue;
	}

	/**
	 * Sets the enableFieldValue
	 *
	 * @param string $enableValue
	 * @return void
	 */
	public function setEnableValue($enableValue) {
		$this->enableValue = $enableValue;
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