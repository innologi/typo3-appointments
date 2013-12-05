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
 * Changes to support properties from properties. This version simply assumes
 * that such are _ALWAYS_ present, hence it is only usable with such fields.
 *
 * e.g.
 * - appointment.address.firstname
 * - appointment.formFieldValues.12.value (where formFieldValues is a storage)
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Appointments_ViewHelpers_Form_TextareaViewHelper extends Tx_Fluid_ViewHelpers_Form_TextareaViewHelper {

	#@LOW make a version that copes well with both normal properties and properties of properties (as well as Select/Textfield)
	/**
	 * Get errors for the property and form name of this view helper
	 *
	 * CHANGES to the original function are marked.
	 *
	 * @return array An array of Tx_Fluid_Error_Error objects
	 * @deprecated since Extbase 1.4.0, will be removed in Extbase 1.6.0.
	 * @see Tx_Fluid_ViewHelpers_Form_AbstractFormViewHelper::getErrorsForProperty()
	 */
	public function getErrorsForProperty() {
		if (!$this->isObjectAccessorMode()) {
			return array();
		}
		$errors = $this->controllerContext->getRequest()->getErrors();
		$formObjectName = $this->viewHelperVariableContainer->get('Tx_Fluid_ViewHelpers_FormViewHelper', 'formObjectName');
		// <!-- CHANGE
			$propertyName = t3lib_div::trimExplode('.',$this->arguments['property'],1);
		// CHANGE -->
		$formErrors = array();
		foreach ($errors as $error) {
			if ($error instanceof Tx_Extbase_Validation_PropertyError && $error->getPropertyName() === $formObjectName) {
				$formErrors = $error->getErrors();
				foreach ($formErrors as $formError) {
					if ($formError instanceof Tx_Extbase_Validation_PropertyError && $formError->getPropertyName() === $propertyName[0]) {
						// <!-- CHANGE
							$propertyErrors = $formError->getErrors();
							foreach ($propertyErrors as $propertyError) {
								//if property of property
								if ($propertyError instanceof Tx_Extbase_Validation_PropertyError && $propertyError->getPropertyName() === $propertyName[1]) {
									return $propertyError->getErrors();
								} elseif ($propertyError instanceof Tx_Appointments_Validation_StorageError) { //if property of storage-property
									$storageErrors = $propertyError->getErrors();
									foreach ($storageErrors as $id=>$storageError) {
										if (is_array($storageError)) {
											foreach($storageError as $storagePropertyError) {
												if ($storagePropertyError instanceof Tx_Extbase_Validation_PropertyError && strval($id) === $propertyName[1] && $storagePropertyError->getPropertyName() === $propertyName[2]) {
													return $storagePropertyError->getErrors();
												}
											}
										}
									}
								}
							}
						// CHANGE -->
					}
				}
			}
		}
		return array();
	}

}

?>