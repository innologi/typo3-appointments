<?php
/***************************************************************
 *  Copyright notice
 *
 *	(c) 2010-2013 Extbase Team (http://forge.typo3.org/projects/typo3v4-mvc)
 *  (c) 2012-2014 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
 *  Extbase is a backport of TYPO3 Flow. All credits go to the TYPO3 Flow team.
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
 * Validator Resolver
 *
 * If dontStoreConjunction is set, will not store the conjunction for the given dataType.
 *
 * In the case of appointments, this is done to ensure an empty $errors property of the conjunction instance,
 * as it would otherwise add up errors from different objects of the same class and show all those
 * duplicates on the frontend.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Appointments_Validation_ValidatorResolver extends Tx_Extbase_Validation_ValidatorResolver {

	/**
	 * Resolves and returns the base validator conjunction for the given data type.
	 *
	 * If no validator could be resolved (which usually means that no validation is necessary),
	 * NULL is returned.
	 *
	 * Changes to the original function are marked. + extra argument
	 *
	 * @param string $dataType The data type to search a validator for. Usually the fully qualified object name
	 * @param boolean $dontStoreConjunction If true, will not store the result or use it from storage
	 * @return Tx_Extbase_Validation_Validator_ConjunctionValidator The validator conjunction or NULL
	 */
	public function getBaseValidatorConjunction($dataType, $dontStoreConjunction = FALSE) {
		// <!-- CHANGE
		if (!isset($this->baseValidatorConjunctions[$dataType]) || $dontStoreConjunction) {
			#@LOW if any of this is going to stay, should look into if I can simply set index with record uid combined
			if (version_compare(TYPO3_branch, '6.2', '<')) {
				$baseValidatorConjunction = $this->buildBaseValidatorConjunction($dataType);
				if (!$dontStoreConjunction) {
					$this->baseValidatorConjunctions[$dataType] = $baseValidatorConjunction;
				}
				return $baseValidatorConjunction;
			} else {
				$this->buildBaseValidatorConjunction($dataType, $dataType);
				$baseValidatorConjunction = $this->baseValidatorConjunctions[$dataType];
				if ($dontStoreConjunction) {
					unset($this->baseValidatorConjunctions[$dataType]);
				}
				return $baseValidatorConjunction;
			}
		}
		// CHANGE -->
		return $this->baseValidatorConjunctions[$dataType];
	}

	/**
	 * Builds a base validator conjunction for the given data type.
	 *
	 * The base validation rules are those which were declared directly in a class (typically
	 * a model) through some validate annotations on properties.
	 *
	 * If a property holds a class for which a base validator exists, that property will be
	 * checked as well, regardless of a validate annotation
	 *
	 * Additionally, if a custom validator was defined for the class in question, it will be added
	 * to the end of the conjunction. A custom validator is found if it follows the naming convention
	 * "Replace '\Model\' by '\Validator\' and append 'Validator'".
	 *
	 * Example: $targetClassName is TYPO3\Foo\Domain\Model\Quux, then the validator will be found if it has the
	 * name TYPO3\Foo\Domain\Validator\QuuxValidator
	 *
	 * @param string $indexKey The key to use as index in $this->baseValidatorConjunctions; calculated from target class name and validation groups
	 * @param string $targetClassName The data type to build the validation conjunction for. Needs to be the fully qualified class name.
	 * @param array $validationGroups The validation groups to build the validator for
	 * @return void
	 * @throws \TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException
	 * @throws \InvalidArgumentException
	 */
	protected function buildBaseValidatorConjunction($indexKey, $targetClassName = NULL, array $validationGroups = array()) {
		if (version_compare(TYPO3_branch, '6.2', '<')) {
			// if <6.2, this change wont be necessary and $indexKey will actually be $dataType
			return parent::buildBaseValidatorConjunction($indexKey);
		}

		$conjunctionValidator = new \TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator();
		$this->baseValidatorConjunctions[$indexKey] = $conjunctionValidator;
		if (class_exists($targetClassName)) {
			// Model based validator
			/** @var \TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator $objectValidator */
			$objectValidator = $this->objectManager->get('TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator', array());
			foreach ($this->reflectionService->getClassPropertyNames($targetClassName) as $classPropertyName) {
				$classPropertyTagsValues = $this->reflectionService->getPropertyTagsValues($targetClassName, $classPropertyName);

				if (!isset($classPropertyTagsValues['var'])) {
					continue;
				}
				try {
					$parsedType = \TYPO3\CMS\Extbase\Utility\TypeHandlingUtility::parseType(trim(implode('', $classPropertyTagsValues['var']), ' \\'));
				} catch (\TYPO3\CMS\Extbase\Utility\Exception\InvalidTypeException $exception) {
					throw new \InvalidArgumentException(sprintf(' @var annotation of ' . $exception->getMessage(), 'class "' . $targetClassName . '", property "' . $classPropertyName . '"'), 1315564744, $exception);
				}
				$propertyTargetClassName = $parsedType['type'];
				/*if (\TYPO3\CMS\Extbase\Utility\TypeHandlingUtility::isCollectionType($propertyTargetClassName) === TRUE) {
				 $collectionValidator = $this->createValidator('TYPO3\CMS\Extbase\Validation\Validator\CollectionValidator', array('elementType' => $parsedType['elementType'], 'validationGroups' => $validationGroups));
				$objectValidator->addPropertyValidator($classPropertyName, $collectionValidator);
				} elseif (class_exists($propertyTargetClassName) && !\TYPO3\CMS\Extbase\Utility\TypeHandlingUtility::isCoreType($propertyTargetClassName) && $this->objectManager->isRegistered($propertyTargetClassName) && $this->objectManager->getScope($propertyTargetClassName) === \TYPO3\CMS\Extbase\Object\Container\Container::SCOPE_PROTOTYPE) {
				$validatorForProperty = $this->getBaseValidatorConjunction($propertyTargetClassName, $validationGroups);
				if (count($validatorForProperty) > 0) {
				$objectValidator->addPropertyValidator($classPropertyName, $validatorForProperty);
				}
				}*/

				$validateAnnotations = array();
				// @todo: Resolve annotations via reflectionService once its available
				if (isset($classPropertyTagsValues['validate']) && is_array($classPropertyTagsValues['validate'])) {
					foreach ($classPropertyTagsValues['validate'] as $validateValue) {
						$parsedAnnotations = $this->parseValidatorAnnotation($validateValue);

						foreach ($parsedAnnotations['validators'] as $validator) {
							array_push($validateAnnotations, array(
							'argumentName' => $parsedAnnotations['argumentName'],
							'validatorName' => $validator['validatorName'],
							'validatorOptions' => $validator['validatorOptions']
							));
						}
					}
				}

				foreach ($validateAnnotations as $validateAnnotation) {
					// @todo: Respect validationGroups
					$newValidator = $this->createValidator($validateAnnotation['validatorName'], $validateAnnotation['validatorOptions']);
					if ($newValidator === NULL) {
						throw new Exception\NoSuchValidatorException('Invalid validate annotation in ' . $targetClassName . '::' . $classPropertyName . ': Could not resolve class name for  validator "' . $validateAnnotation->type . '".', 1241098027);
					}
					$objectValidator->addPropertyValidator($classPropertyName, $newValidator);
				}
			}

			if (count($objectValidator->getPropertyValidators()) > 0) {
				$conjunctionValidator->addValidator($objectValidator);
			}
		}

		$this->addCustomValidators($targetClassName, $conjunctionValidator);
	}

}
?>