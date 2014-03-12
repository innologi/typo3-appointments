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
				$baseValidatorConjunction = $this->buildBaseValidatorConjunction($dataType);
				if (!$dontStoreConjunction) {
					$this->baseValidatorConjunctions[$dataType] = $baseValidatorConjunction;
				}
				return $baseValidatorConjunction;
			}
		// CHANGE -->
		return $this->baseValidatorConjunctions[$dataType];
	}

}
?>