<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
*  All rights reserved
*
*  This class is a backport of the corresponding class of FLOW3.
*  All credits go to the v5 team.
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * A composite of controller arguments
 *
 * @package Appointments
 * @subpackage MVC\Controller
 * @version $ID:$
 */
class Tx_Appointments_MVC_Controller_Arguments extends Tx_Extbase_MVC_Controller_Arguments {
	#@TODO doc all

	/**
	 * Creates, adds and returns a new controller argument to this composite object.
	 * If an argument with the same name exists already, it will be replaced by the
	 * new argument object.
	 *
	 * @param string $name Name of the argument
	 * @param string $dataType Name of one of the built-in data types
	 * @param boolean $isRequired TRUE if this argument should be marked as required
	 * @param mixed $defaultValue Default value of the argument. Only makes sense if $isRequired==FALSE
	 * @return Tx_Appointments_MVC_Controller_Argument The new argument
	 */
	public function addNewArgument($name, $dataType = 'Text', $isRequired = FALSE, $defaultValue = NULL) {
		$argument = $this->objectManager->create('Tx_Appointments_MVC_Controller_Argument', $name, $dataType);
		$argument->setRequired($isRequired);
		$argument->setDefaultValue($defaultValue);
		$this->addArgument($argument);
		return $argument;
	}

}
?>