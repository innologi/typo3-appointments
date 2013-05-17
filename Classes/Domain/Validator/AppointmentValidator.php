<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * Appointment Domain Validator.
 *
 * A specialized validator to set domain-specific variables necessary for validation.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Validator_AppointmentValidator extends Tx_Appointments_Domain_Validator_ObjectPropertiesValidator { #@TODO __technically shouldn't extend ObjectProperties

	/**
	 * Sets some variable validationType properties, before passing it to parent.
	 *
	 * @param mixed $appointment The object instance to validate
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	public function isValid($appointment) { #@TODO __doc all
		$valid = FALSE;
		if ($appointment instanceof Tx_Appointments_Domain_Model_Appointment) {
			$type = $appointment->getType();

			if (is_object($type)) {
				if ($type->getAddressDisable()) {
					$valid = TRUE;
				} else {
					$validatorResolver = $this->objectManager->get('Tx_Extbase_Validation_ValidatorResolver');
					$validator = $validatorResolver->createValidator('Tx_Appointments_Domain_Validator_ObjectPropertiesValidator');
					$validatorConjunction = $this->objectManager->get('Tx_Extbase_Validation_Validator_ConjunctionValidator');
					$validatorConjunction->addValidator($validator);

					if ($validatorConjunction->isValid($appointment->getAddress())) {
						$valid = TRUE;
					} else {
						$propertyError = new Tx_Extbase_Validation_PropertyError('address');
						$propertyError->addErrors($validator->getErrors());
						$this->errors[] = $propertyError;
					}
				}
			} //if type is not an object, something is already terribly wrong and there will be a catched exception anyway, so no need to do it here as well

		}
		return $valid;
	}
	#@TODO __can we do formfieldvalue.value the same way? Rather not use the variableValidator hacks if not necessary..

}
?>