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
 * Variable Validator Resolver
 *
 * Provides the ability to resolve property names in @validate option values.
 * Effectively, this means you can assign variable option values to validators.
 *
 * Drawback of this method is, it relies on setClassInstance() to be called prior
 * to getBaseValidatorConjunction() which in turn needs to have $noStorage set to
 * TRUE, in order for the resolver to have access to object properties.
 *
 * @validate syntax:
 * - ValidatorName(param=$property)
 * - ValidatorName(param=$property::subProperty::subSubProperty)
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Appointments_Validation_VariableValidatorResolver extends Tx_Extbase_Validation_ValidatorResolver {

	/**
	 * Class instance from which the property-to-be-validated comes from
	 *
	 * @var object
	 */
	protected $classInstance;

	/**
	 * Sets Class Instance
	 *
	 * @param object $object
	 * @return void
	 */
	public function setClassInstance($object) {
		$this->classInstance = $object;
	}

	/**
	 * Returns Class Instance
	 *
	 * @return object
	 */
	public function getClassInstance() {
		return $this->classInstance;
	}

	/**
	 * Resolves and returns the base validator conjunction for the given data type.
	 *
	 * If no validator could be resolved (which usually means that no validation is necessary),
	 * NULL is returned.
	 *
	 * Changes to the original function are marked. + extra argument
	 *
	 * @param string $dataType The data type to search a validator for. Usually the fully qualified object name
	 * @param boolean $noStorage If true, will not store the result or use it from storage
	 * @return Tx_Extbase_Validation_Validator_ConjunctionValidator The validator conjunction or NULL
	 */
	public function getBaseValidatorConjunction($dataType, $noStorage = FALSE) {
		// <!-- CHANGE
			if (!isset($this->baseValidatorConjunctions[$dataType]) || $noStorage) {
				$baseValidatorConjunction = $this->buildBaseValidatorConjunction($dataType);
				if (!$noStorage) {
					$this->baseValidatorConjunctions[$dataType] = $baseValidatorConjunction;
				}
				return $baseValidatorConjunction;
			}
		// CHANGE -->
		return $this->baseValidatorConjunctions[$dataType];
	}

	/**
	 * Parses $rawValidatorOptions not containing quoted option values.
	 * $rawValidatorOptions will be an empty string afterwards (pass by ref!).
	 *
	 * Changes to the original function are marked.
	 *
	 * @param string &$rawValidatorOptions
	 * @return array An array of optionName/optionValue pairs
	 */
	protected function parseValidatorOptions($rawValidatorOptions) {
		$validatorOptions = array();
		$parsedValidatorOptions = array();
		preg_match_all(self::PATTERN_MATCH_VALIDATOROPTIONS, $rawValidatorOptions, $validatorOptions, PREG_SET_ORDER);
		foreach ($validatorOptions as $validatorOption) {
			// <!-- CHANGE
				$optionValue = $this->resolveVariableOptionValue(trim($validatorOption['optionValue']));
				$parsedValidatorOptions[trim($validatorOption['optionName'])] = $optionValue;
			// CHANGE -->
		}
		array_walk($parsedValidatorOptions, array($this, 'unquoteString'));
		return $parsedValidatorOptions;
	}

	/**
	 * Resolves a variable option value referring to a property within
	 * the same object or an object property.
	 *
	 * @param string $optionValue The original option value
	 * @return string The resolved option value
	 */
	protected function resolveVariableOptionValue($optionValue) {
		if (strpos($optionValue,'$',0) !== FALSE) {
			$optionValue = ltrim($optionValue,'$');
			$parts = t3lib_div::trimExplode('::',$optionValue,1);
			$propertyValue = $this->getClassInstance();
			foreach ($parts as $part) {
				$propertyValue = Tx_Extbase_Reflection_ObjectAccess::getProperty($propertyValue, $part);
			}
			$optionValue = $propertyValue;
		}
		return $optionValue;
	}

}
?>