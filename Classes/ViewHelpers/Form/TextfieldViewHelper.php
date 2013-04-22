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
class Tx_Appointments_ViewHelpers_Form_TextfieldViewHelper extends Tx_Fluid_ViewHelpers_Form_TextfieldViewHelper {

	#@TODO doc
	/**
	 * Renders the textfield.
	 *
	 * @param boolean $required If the field is required or not
	 * @param string $type The field type, e.g. "text", "email", "url" etc.
	 * @param string $placeholder A string used as a placeholder for the value to enter
	 * @return string
	 */
	public function render($required = NULL, $type = 'text', $placeholder = NULL) {
		if ($required !== TRUE) {
			$required = NULL;
		}
		if (!isset($placeholder[0])) {
			$placeholder = NULL;
		}
		return parent::render($required,$type,$placeholder);
	}

	#@TODO doc
	/**
	 * Get the value of this form element.
	 * Either returns arguments['value'], or the correct value for Object Access.
	 *
	 * @param boolean $convertObjects whether or not to convert objects to identifiers
	 * @return mixed Value
	 */
	protected function getValue($convertObjects = TRUE) {
		if ($this->arguments instanceof Tx_Fluid_Core_ViewHelper_Arguments) { //TYPO3 4.5 compatibility
			return $this->getOrChangeValue($convertObjects);
		} else {
			if ($this->arguments['value'] === NULL) {
				$this->arguments['value'] = '';
			}
			return parent::getValue($convertObjects);
		}
	}

	/**
	 * Get the value of this form element.
	 * Either returns arguments['value'], or the correct value for Object Access.
	 *
	 * @param boolean $convertObjects whether or not to convert objects to identifiers
	 * @return mixed Value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function getOrChangeValue($convertObjects = TRUE) { #@TODO doc
		$value = NULL;
		if ($this->arguments->hasArgument('value')) {
			$value = $this->arguments['value'];
		// <!-- CHANGE
		} elseif ($this->arguments['value'] === NULL) {
			$value = '';
		// CHANGE -->
		} elseif ($this->isObjectAccessorMode() && $this->viewHelperVariableContainer->exists('Tx_Fluid_ViewHelpers_FormViewHelper', 'formObject')) {
			$this->addAdditionalIdentityPropertiesIfNeeded();
			$value = $this->getPropertyValue();
		}
		if ($convertObjects === TRUE && is_object($value)) {
			$identifier = $this->persistenceManager->getBackend()->getIdentifierByObject($value);
			if ($identifier !== NULL) {
				$value = $identifier;
			}
		}
		return $value;
	}

	#@SHOULD make a version that copes well with both normal properties and properties of properties
	/**
	 * Get errors for the property and form name of this view helper
	 *
	 * Changes to the original function are marked.
	 *
	 * @return array An array of Tx_Fluid_Error_Error objects
	 * @deprecated since Extbase 1.4.0, will be removed in Extbase 1.6.0.
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