<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * FormFieldVlaue Domain Validator.
 *
 * A domain-specific validator doesn't "replace" the normal validator, but instead complements it.
 * This validator tests whether $value needs to be validated, and if so, runs through the configured validators.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Validator_FormFieldValueValidator extends Tx_Appointments_Validation_Validator_PreppedAbstractValidator {

	/**
	 * Tests if special properties are valid.
	 *
	 * @param mixed $formFieldValue The object instance to validate
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	public function isValid($formFieldValue) {
		$valid = FALSE;
		if ($formFieldValue instanceof Tx_Appointments_Domain_Model_FormFieldValue) {
			$valid = $this->validateValue($formFieldValue->getValue(), $formFieldValue->getFormField());
		}
		return $valid;
	}

	/**
	 * Validates value, based on the variable formField.validationTypes
	 *
	 * If $formfield is not as expected, something is already terribly wrong
	 * and there will be a catched exception anyway, so no need to catch
	 * that here.
	 *
	 * @param string $value The value to validate
	 * @param Tx_Appointments_Domain_Model_FormField $formField
	 * @return boolean TRUE if valid, FALSE if invalid
	 */
	protected function validateValue($value, Tx_Appointments_Domain_Model_FormField $formField) {
		$validationTypes = t3lib_div::trimExplode(',', $formField->getValidationTypes(), TRUE);
		$validatorResolver = $this->objectManager->get('Tx_Extbase_Validation_ValidatorResolver');
		$validatorConjunction = $this->objectManager->get('Tx_Extbase_Validation_Validator_ConjunctionValidator');
		$addNotEmpty = FALSE;

		if (!empty($validationTypes)) {
			$addNotEmpty = TRUE;
			foreach ($validationTypes as $validationType) {
				$validationType = intval($validationType);
				#@TODO currently tells FE the field is required if ANY of these is selected, but alphanum, string and text don't require it to be, so they're disabled in TCA. Fix this as soon as extbase is consistent @ its Validators.
				switch ($validationType) {
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_ALPHANUMERIC: #@LOW move these outside of formfield and into the validator to lose the formfield dependence
						$validator = $validatorResolver->createValidator('Alphanumeric');
						break;
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_DATE_TIME:
						$validator = $validatorResolver->createValidator('DateTime');
						//$value is of type string, while this validator tests it for objecttype DateTime
						if (!empty($value)) {
							try {
								$newValue = new DateTime($value);
								//results in NULL if a valid DateTime string but not in the specified format, so that you can't get away with a timestamp or some other format
								$value = ($value === $newValue->format('d-m-Y')) ? $newValue : NULL;
							} catch (Exception $e) { //if $value is no valid DateTime string
								$value = NULL;
							}
							if ($value === NULL && version_compare(TYPO3_branch, '6.2', '>=')) {
								// something other than DateTime is good enough
								$value = 1;
							}
						}
						break;
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_EMAIL_ADDRESS:
						$validator = $validatorResolver->createValidator('EmailAddress');
						break;
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_FLOAT:
						$validator = $validatorResolver->createValidator('Float');
						break;
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_INTEGER:
						$validator = $validatorResolver->createValidator('Integer');
						break;
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_NOT_EMPTY:
						$validator = $validatorResolver->createValidator('NotEmpty');
						$addNotEmpty = FALSE;
						break;
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_NUMBER:
						$validator = $validatorResolver->createValidator('Number');
						break;
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_STRING:
						$validator = $validatorResolver->createValidator('String');
						break;
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_TEXT:
						$validator = $validatorResolver->createValidator('Text');
						break;
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_NATURALNUMBER:
						//without options, it sets startRange = 0 and endRange = PHP_INT_MAX
						$validator = $validatorResolver->createValidator('NumberRange');
						break;
				}
				$validatorConjunction->addValidator($validator);
			}
		}

		if (version_compare(TYPO3_branch, '6.2', '<')) {
			if ($validatorConjunction->isValid($value)) {
				return TRUE;
			}
			$errors = $validatorConjunction->getErrors();
		} else {
			#@TODO remove this crap once we go 6.2-only
			// temporary change to enforce consistency between TYPO3 versions
			if ($addNotEmpty) {
				$validatorConjunction->addValidator($validatorResolver->createValidator('NotEmpty'));
			}

			$result = $validatorConjunction->validate($value);
			if (!$result->hasErrors()) {
				return TRUE;
			}
			$errors = $result->getErrors();
		}

		$propertyError = new Tx_Extbase_Validation_PropertyError('value');
		$propertyError->addErrors($errors);
		$this->errors[] = $propertyError;
		return FALSE;
	}

}
?>