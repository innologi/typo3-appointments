<?php
namespace Innologi\Appointments\Domain\Validator;
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
use Innologi\Appointments\Validation\Validator\PreppedAbstractValidator;
use Innologi\Appointments\Mvc\Exception\PropertyDeleted;
use Innologi\Appointments\Validation\ValidatorResolver;
/**
 * Object Properties Validator, validates an object based on its properties' validation.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class ObjectPropertiesValidator extends PreppedAbstractValidator {

	/**
	 * Checks if an object is valid according to all its properties by passing
	 * the object through the BaseValidatorConjunction from the Extbase
	 * Validator Resolver.
	 *
	 * If at least one error occurred, the result is FALSE and all property-errors will
	 * be merged with $this->errors.
	 *
	 * @param mixed $value The value that should be validated
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 * @throws PropertyDeleted
	 */
	public function isValid($value) {
		if (!is_object($value)) { //also works on objectStorage objects
			throw new PropertyDeleted();
		}

		#@TODO if this isn't solved by the rewrittenPropertyMapper (which I think it is, looking at validate()), consider using DI to overwrite the conjunction class instead to add an error-clearing method
		$validatorResolver = $this->objectManager->get(ValidatorResolver::class); //the original resolver creates a single instance of the conjunction which accumulates errors, so we use our own
		$dontStore = (bool)$this->options['clearErrors']; //TRUE enables the workaround that prevents multiple same-class instances to accumulate their siblings errors

		$validator = $validatorResolver->getBaseValidatorConjunction(get_class($value), $dontStore);
		if ($validator->isValid($value)) {
			return TRUE;
		}

		// the validator will be created only once, which means errors start piling up from different objects if we don't empty the array
		$this->errors = $validator->getErrors();
		return FALSE;
	}

}