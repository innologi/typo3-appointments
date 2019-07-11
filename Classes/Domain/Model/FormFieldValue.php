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
/**
 * FormFieldValue domain model
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FormFieldValue extends AbstractEntity {

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
	 * @var \Innologi\Appointments\Domain\Model\FormField
	 * @extensionScannerIgnoreLine
	 * @validate NotEmpty
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
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
	 * @return \Innologi\Appointments\Domain\Model\FormField
	 */
	public function getFormField() {
		return $this->formField;
	}

	/**
	 * Sets the formField
	 *
	 * @param \Innologi\Appointments\Domain\Model\FormField $formField
	 * @return void
	 */
	public function setFormField(FormField $formField) {
		$this->formField = $formField;
	}

}