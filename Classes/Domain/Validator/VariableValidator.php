<?php
namespace Innologi\Appointments\Domain\Validator;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Innologi\Appointments\Domain\Model\{FormField, FormFieldValue};
use TYPO3\CMS\Extbase\Object\ObjectManager;
/**
 * FormFieldVlaue Domain Validator.
 *
 * A domain-specific validator doesn't "replace" the normal validator, but instead complements it.
 * This validator tests whether $value needs to be validated, and if so, runs through the configured validators.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class VariableValidator extends AbstractValidator {

	/**
	 * @var ObjectManager
	 */
	protected $objectManager;

	/**
	 * Gets ObjectManager
	 *
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected function getObjectManager() {
		if ($this->objectManager === NULL) {
			$this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		}
		return $this->objectManager;
	}

	/**
	 * Checks if the given value is valid according to the validator, and returns
	 * the error messages object which occurred.
	 *
	 * @param mixed $value The value that should be validated
	 * @return \TYPO3\CMS\Extbase\Error\Result
	 * @api
	 */
	public function validate($value)
	{
		$this->result = new \TYPO3\CMS\Extbase\Error\Result();
		//if ($this->acceptsEmptyValues === false || $this->isEmpty($value) === false) {
			$this->isValid($value);
		//}
		return $this->result;
	}

	/**
	 * Tests if special properties are valid.
	 *
	 * @param mixed $formFieldValue The object instance to validate
	 * @return void
	 */
	protected function isValid($formFieldValue) {
		if ($formFieldValue instanceof FormFieldValue) {
			$this->validateValue($formFieldValue->getValue(), $formFieldValue->getFormField());
		}
	}
	// @LOW make it more general use ready, so we can put it in our lib
	/**
	 * Validates value, based on the variable formField.validationTypes
	 *
	 * If $formfield is not as expected, something is already terribly wrong
	 * and there will be a catched exception anyway, so no need to catch
	 * that here.
	 *
	 * @param string $value The value to validate
	 * @param \Innologi\Appointments\Domain\Model\FormField $formField
	 * @return void
	 */
	protected function validateValue($value, FormField $formField) {
		$validationTypes = $formField->getValidationTypesArray();
		if (empty($validationTypes)) {
			// nothing to validate
			return;
		}

		/** @var ValidatorResolver $validatorResolver */
		$validatorResolver = $this->getObjectManager()->get(ValidatorResolver::class);
		/** @var ConjunctionValidator $validatorConjunction */
		$validatorConjunction = $this->getObjectManager()->get(ConjunctionValidator::class);

		$required = FALSE;
		foreach ($validationTypes as $validationType) {
			$validationType = intval($validationType);
			#@TODO currently tells FE the field is required if ANY of these is selected, but alphanum, string and text don't require it to be, so they're disabled in TCA. Fix this as soon as extbase is consistent @ its Validators.
			switch ($validationType) {
				case FormField::VALIDATE_ALPHANUMERIC: #@LOW move these outside of formfield and into the validator to lose the formfield dependence
					$validator = $validatorResolver->createValidator('Alphanumeric');
					break;
				case FormField::VALIDATE_DATE_TIME:
					$validator = $validatorResolver->createValidator('DateTime');
					// $value is of type string, while this validator tests it for objecttype DateTime
						// and we don't have the propertymapper available to us in this variable validator-case
					if (!empty($value)) {
						try {
							$newValue = new \DateTime($value);
							// @TODO configurable date formats all throughout the extension
							//results in NULL if a valid DateTime string but not in the specified format, so that you can't get away with a timestamp or some other format
							$value = ($value === $newValue->format('d-m-Y')) ? $newValue : 1;
						} catch (\Exception $e) { //if $value is no valid DateTime string
							$value = 1;
						}
					}
					break;
				case FormField::VALIDATE_EMAIL_ADDRESS:
					$validator = $validatorResolver->createValidator('EmailAddress');
					break;
				case FormField::VALIDATE_FLOAT:
					$validator = $validatorResolver->createValidator('Float');
					break;
				case FormField::VALIDATE_INTEGER:
					$validator = $validatorResolver->createValidator('Integer');
					break;
				case FormField::VALIDATE_NOT_EMPTY:
					$validator = $validatorResolver->createValidator('NotEmpty');
					$required = TRUE;
					break;
				case FormField::VALIDATE_NUMBER:
					$validator = $validatorResolver->createValidator('Number');
					break;
				case FormField::VALIDATE_STRING:
					$validator = $validatorResolver->createValidator('String');
					break;
				case FormField::VALIDATE_TEXT:
					$validator = $validatorResolver->createValidator('Text');
					break;
				case FormField::VALIDATE_NATURALNUMBER:
					//without options, it sets startRange = 0 and endRange = PHP_INT_MAX
					$validator = $validatorResolver->createValidator('NumberRange');
					break;
			}
			$validatorConjunction->addValidator($validator);
		}

		// if NotEmpty is not set, and $value is empty, stop validation
		if (!$required && $this->isEmpty($value)) {
			return;
		}

		$originalResult = $validatorConjunction->validate($value);
		// we add our own version of the errors, with an included formfield label as title
		if ($originalResult->hasMessages()) {
			foreach ($originalResult->getErrors() as $error) {
				/** @var \TYPO3\CMS\Extbase\Validation\Error $error */
				$this->addError($error->getMessage(), $error->getCode(), $error->getArguments(), $formField->getLabel());
			}
		}
	}

}