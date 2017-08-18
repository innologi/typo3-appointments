<?php
namespace Innologi\Appointments\Domain\Validator;
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
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use Innologi\Appointments\Validation\Validator\PreppedAbstractValidator;
use Innologi\Appointments\Domain\Model\{Appointment, Type};
/**
 * Appointment Domain Validator.
 *
 * A domain-specific validator doesn't "replace" the normal validator, but instead complements it.
 * This validator tests whether $address needs to be validated, and if so, runs through it.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class AppointmentValidator extends PreppedAbstractValidator {

	/**
	 * Test if address needs to be validated, and if so, run through it.
	 *
	 * @param mixed $appointment The object instance to validate
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	public function isValid($appointment) {
		$valid = FALSE;
		if ($appointment instanceof Appointment) {
			$type = $appointment->getType();

			if ($type instanceof Type) {
				if ($type->getAddressDisable()) {
					//address is not to be tested
					$valid = TRUE;
				} else {
					//address needs to validate, or appointment isn't valid
					$validatorResolver = $this->objectManager->get(ValidatorResolver::class);
					$validator = $validatorResolver->createValidator(ObjectPropertiesValidator::class);

					if ($validator->isValid($appointment->getAddress())) {
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

}