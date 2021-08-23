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
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use Innologi\Appointments\Domain\Model\Appointment;
use Innologi\Appointments\Mvc\Exception\PropertyDeleted;
/**
 * Appointment Domain Validator.
 *
 * A domain-specific validator doesn't "replace" the normal validator, but instead complements it.
 * This validator tests whether formfields needs to be validated, and if so, runs through them.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class AppointmentValidator extends AbstractValidator {

	/**
	 * @var ValidatorResolver
	 */
	protected $validatorResolver;

	/**
	 * Gets ValidatorResolver
	 *
	 * @return ValidatorResolver
	 */
	protected function getValidatorResolver() {
		if ($this->validatorResolver === NULL) {
			$this->validatorResolver = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				\TYPO3\CMS\Extbase\Object\ObjectManager::class
			)->get(ValidatorResolver::class);
		}
		return $this->validatorResolver;
	}

	/**
	 * Test if address needs to be validated, and if so, run through it.
	 *
	 * @param Appointment $appointment The object instance to validate
	 * @return void
	 */
	protected function isValid($appointment) {
		// @LOW ___did we need this check at some point during 4.5? seems rather.. redundant. see if we can get rid of it
		if ($appointment instanceof Appointment) {
			// address validation --no longer necessary since extbase validation has matured
			/*$type = $appointment->getType();
			if ($type instanceof Type) {
				if (!$type->getAddressDisable()) {
					//address needs to validate, or appointment isn't valid
					$validator = $this->getValidatorResolver()->getBaseValidatorConjunction(Address::class);
					$result = $validator->validate($appointment->getAddress());
					if ($result->hasMessages()) {
						$this->result->forProperty('address')->merge($result);
					}
				}
			}*/ //if type is not an object, something is already terribly wrong and there will be a catched exception anyway, so no need to do it here as well

			// formfieldvalue validation
			$formFieldValues = $appointment->getFormFieldValues()->toArray();
			if (!empty($formFieldValues)) {
				// registers values referred to by delayed objects
				$valueRegister = [];
				// adds a second pass for objects that need to be checked
					// AFTER everything else has had their values registered
				$delayedObjects = [];
				$objects = [$formFieldValues, &$delayedObjects];
				foreach ($objects as $container) {
					/** @var \Innologi\Appointments\Domain\Model\FormFieldValue $formFieldValue */
					foreach ($container as $formFieldValue) {
						$formField = $formFieldValue->getFormField();
						// although AppointmentController takes care of this, it has happened that a type was edited while an appointment was being finished
						if ($formField === NULL) {
							throw new PropertyDeleted();
						}

						// if the formfield is enabled by another, we'll need to exclude
							// or delay its validation depending on the conditions
						if (($enableField = $formField->getEnableField()) !== NULL) {
							$enablerUid = $enableField->getUid();
							// if the field on which we depend hasn't passed, save this one for the second run,
								// note that this solution means we explicitly cannot enable multi-layered enable-fields
								// and that our TCA attempts to prevent this
							if (!isset($valueRegister[$enablerUid])) {
								$delayedObjects[] = $formFieldValue;
								continue;
							}

							// if the field isn't enabled, let it skip validation
							if ($valueRegister[$enablerUid] !== strtolower($formField->getEnableValue())) {
								continue;
							}
						} else {
							// since we don't support multi-layered enable-fields, we may register only if no enable field is set
							$valueRegister[$formField->getUid()] = strtolower($formFieldValue->getValue());
						}

						$validator = $this->getValidatorResolver()->createValidator(VariableValidator::class);
						$result = $validator->validate($formFieldValue);
						if ($result->hasMessages()) {
							$this->result->forProperty('formFieldValues.i' . $formField->getUid() . '.value')->merge($result);
						}
					}
				}
			}
		}
	}

}