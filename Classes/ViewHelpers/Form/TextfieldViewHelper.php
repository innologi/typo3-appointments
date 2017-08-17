<?php
namespace Innologi\Appointments\ViewHelpers\Form;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper;
use Innologi\Appointments\Validation\StorageError;
/**
 * Changes to support properties from properties. This version simply assumes
 * that such are _ALWAYS_ present, hence it is only usable with such fields.
 *
 * e.g.
 * - appointment.address.firstname
 * - appointment.formFieldValues.12.value (where formFieldValues is a storage)
 *
 * Also addresses some usability concerns.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TextfieldViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\TextfieldViewHelper {

	/**
	 * Renders the textfield.
	 *
	 * CHANGES:
	 * Adds some flexibility in templates with the 'required' and 'placeholder' attributes,
	 * allowing for conditions as values that return either a boolean or an empty string.
	 *
	 * @param boolean $required If the field is required or not
	 * @param string $type The field type, e.g. "text", "email", "url" etc.
	 * @param string $realPlaceholder A string used as a placeholder for the value to enter
	 * @return string
	 */
	public function render($required = NULL, $type = 'text', $realPlaceholder = NULL) {
		$required = ($required === TRUE) ? 'required' : NULL;

		if (version_compare(TYPO3_branch, '6.2', '<')) {
			if (!isset($realPlaceholder[0])) {
				$realPlaceholder = NULL;
			}
			return parent::render($required, $type, $realPlaceholder);
		}

		if (isset($realPlaceholder[0])) {
			if ($this->tag->hasAttribute('placeholder')) {
				$this->tag->removeAttribute('placeholder');
			}
			$this->tag->addAttribute('placeholder', $realPlaceholder);
		}
		return parent::render($required,$type);
	}

	/**
	 * Get the value of this form element.
	 * Either returns arguments['value'], or the correct value for Object Access.
	 *
	 * CHANGES:
	 * Adds support for emptying a field value that already contained a value.
	 * Normally, an empty value would be ignored. This change produces some warnings
	 * in TYPO3 4.5 however, so there is also an added layer of compatibility.
	 *
	 * @param boolean $convertObjects whether or not to convert objects to identifiers
	 * @return mixed Value
	 */
	protected function getValue($convertObjects = TRUE) {
		if ($this->arguments['value'] === NULL) {
			$this->arguments['value'] = '';
		}
		return parent::getValue($convertObjects);
	}

	/**
	 * Get the value of this form element.
	 * Either returns arguments['value'], or the correct value for Object Access.
	 *
	 * CHANGES:
	 * This function is primarily added for the most transparent TYPO3 4.5 compatibility
	 * possible to the change made in getValue(). The changes to the original 4.5 function
	 * are marked.
	 *
	 * @param boolean $convertObjects whether or not to convert objects to identifiers
	 * @return mixed Value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @see getValue()
	 */
	protected function getOrChangeValue($convertObjects = TRUE) {
		$value = NULL;
		if ($this->arguments->hasArgument('value')) {
			$value = $this->arguments['value'];
		// <!-- CHANGE
		} elseif ($this->arguments['value'] === NULL) {
			$value = '';
		// CHANGE -->
		} elseif ($this->isObjectAccessorMode() && $this->viewHelperVariableContainer->exists(FormViewHelper::class, 'formObject')) {
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

	/**
	 * Get errors for the property and form name of this view helper
	 *
	 * CHANGES to the original function are marked.
	 *
	 * @return array An array of Tx_Fluid_Error_Error objects
	 * @deprecated since Extbase 1.4.0, will be removed in Extbase 1.6.0.
	 */
	public function getErrorsForProperty() {
		if (!$this->isObjectAccessorMode()) {
			return array();
		}
		$errors = $this->controllerContext->getRequest()->getErrors();
		$formClass = version_compare(TYPO3_branch, '6.0', '>=') ? 'TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper' : 'Tx_Fluid_ViewHelpers_FormViewHelper';
		$formObjectName = $this->viewHelperVariableContainer->get($formClass, 'formObjectName');
		// <!-- CHANGE
			$propertyName = GeneralUtility::trimExplode('.',$this->arguments['property'],1);
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
								} elseif ($propertyError instanceof StorageError) { //if property of storage-property
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