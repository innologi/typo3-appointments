<?php
namespace Innologi\Appointments\Validation;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\Validation\Error;
/**
 * This object holds validation errors for an entire object storage.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class StorageError extends Error {

	/**
	 * @var string The default (english) error message.
	 */
	protected $message = 'Validation errors for storage "%s"';

	/**
	 * @var string The error code
	 */
	protected $code = 133713371337;

	/**
	 * @var string The property name
	 */
	protected $propertyName;

	/**
	 * @var array An array of Tx_Extbase_Validation_Error for the property
	 */
	protected $errors = array();

	/**
	 * Create a new property error with the given property name
	 *
	 * @param string $propertyName The property name
	 */
	public function __construct($propertyName) {
		$this->propertyName = $propertyName;
		$this->message = sprintf($this->message, $propertyName);
	}

	/**
	 * Add errors
	 *
	 * @param string $identifier ID of storage object to which the errors belong
	 * @param array $errors Array of Tx_Extbase_Validation_Error for the property
	 * @return void
	 */
	public function addErrors($identifier,$errors) {
		$this->errors[$identifier] = $errors;
	}

	/**
	 * Get all errors for the property
	 *
	 * @return array An array of Tx_Extbase_Validation_Error objects or an empty array if no errors occured for the property
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Get the property name
	 * @return string The property name for this error
	 */
	public function getPropertyName() {
		return $this->propertyName;
	}
}

?>