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
 * Error On Debug Controller.
 *
 * Hides the standard Extbase error flash message, unless FE debug is enabled.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_MVC_Controller_ErrorOnDebugController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * A template method for displaying custom error flash messages, or to
	 * display no flash message at all on errors. Override this to customize
	 * the flash message in your action controller.
	 *
	 * @return string|boolean The flash message or FALSE if no flash message should be set
	 */
	/**
	 * A template method for displaying custom error flash messages, or to
	 * display no flash message at all on errors. Override this to customize
	 * the flash message in your action controller.
	 *
	 * @return string|boolean The flash message or FALSE if no flash message should be set
	 */
	protected function getErrorFlashMessage() {
		global $TYPO3_CONF_VARS;
		$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][strtolower($this->extensionName)]);
		return isset($extConf['debug']) && $extConf['debug'] ? parent::getErrorFlashMessage() : FALSE; #@TODO can't we make it rely on a TYPO3 general debug var? (global displayErrors?)
	}
	/*protected function getErrorFlashMessage() {
		global $TYPO3_CONF_VARS;
		if (isset($TYPO3_CONF_VARS['FE']['debug']) && $TYPO3_CONF_VARS['FE']['debug']) {
			return parent::getErrorFlashMessage();
		} else {
			return FALSE;
		}
	}*/

}
?>