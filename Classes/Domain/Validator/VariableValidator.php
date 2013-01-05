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
 * Variable Validator, performs a switch/case to determine the validators requested
 * in its options. Facilitates validation for customizable fields for which the
 * validators are not determined by the user instead of the developer.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Validator_VariableValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {

	/**
	 * @var $objectManager Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * injectObjectManager
	 *
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Checks if an object is valid according to the chosen validators.
	 * Requires the option 'validationTypes' to work.
	 *
	 * @param mixed $value The value that should be validated
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	public function isValid($value) {
		if (isset($this->options['validationTypes'])) {
			$validationTypes = t3lib_div::trimExplode(',', $this->options['validationTypes'], 1, 9);
			$validatorResolver = $this->objectManager->get('Tx_Appointments_Validation_VariableValidatorResolver');
			$validatorConjunction = $this->objectManager->get('Tx_Extbase_Validation_Validator_ConjunctionValidator');

			foreach ($validationTypes as $validationType) {
				$validationType = intval($validationType);
				#@TODO currently tells FE the field is required if ANY of these is selected, but alphanum, string and text don't require it to be, so they're disabled in TCA. Fix this as soon as extbase is consistent @ its Validators.
				switch ($validationType) {
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_ALPHANUMERIC:
						$validator = $validatorResolver->createValidator('Alphanumeric');
						break;
					case Tx_Appointments_Domain_Model_FormField::VALIDATE_DATE_TIME:
						$validator = $validatorResolver->createValidator('DateTime');
						//$value is of type string, while this validator tests it for objecttype DateTime
						if (!empty($value)) {
							try {
								$value = new DateTime($value);
							} catch (Exception $e) { //if $value is no valid DateTime string
								$value = NULL;
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
				}
				$validatorConjunction->addValidator($validator);
			}

			if ($validatorConjunction->isValid($value)) {
				return TRUE;
			}
			$this->errors = array_merge($this->errors,$validatorConjunction->getErrors());
		}

		#@SHOULD throw exception when option isn't set, instead of just returning FALSE

		return FALSE;
	}

}
?>