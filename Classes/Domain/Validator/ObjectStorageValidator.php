<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * Object Storage Validator, validates an ObjectStorage's objects.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Domain_Validator_ObjectStorageValidator extends Tx_Appointments_Validation_Validator_PreppedAbstractValidator {

	/**
	 * Checks if an object is a valid objectstorage by passing all its objects
	 * through the objectsPropertiesValidator. Options are passed to the
	 * objectsPropertiesValidator.
	 *
	 * Utilizes a StorageError to help us differentiate @ form.error viewhelper.
	 *
	 * If at least one error occurred, the result is FALSE and all property-errors will
	 * be merged with $this->errors.
	 *
	 * @param mixed $value The value that should be validated
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	public function isValid($value) {
		$valid = FALSE;
		$storageError = NULL;

		if ($value instanceof Tx_Extbase_Persistence_ObjectStorage) {
			$validator = $this->objectManager->get('Tx_Appointments_Validation_VariableValidatorResolver')->createValidator('Tx_Appointments_Domain_Validator_ObjectPropertiesValidator',$this->options);
			$valid = TRUE;
			foreach ($value as $obj) {
				if (!$validator->isValid($obj)) {
					$valid = FALSE;

					if (!isset($storageError)) {
						$propertyName = str_replace('Tx_Appointments_Domain_Model_','',get_class($obj));
						$propertyName[0] = strtolower($propertyName[0]);
						$storageError = new Tx_Appointments_Validation_StorageError($propertyName);
					}

					$storageError->addErrors($obj->getFormField()->getUid(), $validator->getErrors()); #@LOW "getFormField" should be variable
				}
			}
			$this->errors[] = $storageError;
		}
		return $valid;
	}

}
?>