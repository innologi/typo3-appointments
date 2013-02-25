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
 * Settings Override Controller.
 *
 * Provides some improvements/changes to the Extbase Action Controller,
 * serving as a base for this package's domain controllers. Its main
 * feature is more customization to how/when Flexform settings override
 * TypoScript settings and vice versa.
 *
 * Furthermore, it disables the flasherror messages and provides a (necessary)
 * try and catch construction for resolving controller arguments from the
 * database.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_MVC_Controller_SettingsOverrideController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * Injects the configuration manager and resolves the plugin settings.
	 *
	 * Instead of letting the flexform dominate the plugin settings, your TypoScript
	 * can now control which ones don't. It supports the following TypoScript, each
	 * taking a comma-separated list of (settings.)field names:
	 *
	 * plugin.[extname].tsOverride {
	 *   checkFields: if the flexform field value equals '--TYPOSCRIPT--', takes the value from TypoScript instead
	 *   selectFields: flexform field value contains field names which are taken from TypoScript
	 * }
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;

		$settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		$ts = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

		$extensionName = strtolower($this->extensionName);
		if (isset($ts['plugin.']['tx_' . $extensionName . '.']['tsOverride.']) && isset($ts['plugin.']['tx_' . $extensionName . '.']['settings.'])) {
			$tsOverride = $ts['plugin.']['tx_' . $extensionName . '.']['tsOverride.'];
			$tsSettings = $ts['plugin.']['tx_' . $extensionName . '.']['settings.'];

			//looks for TS values if flexform value === '--TYPOSCRIPT--'
			if (isset($tsOverride['checkFields'])) {
				$fields = t3lib_div::trimExplode(',',$tsOverride['checkFields'],1);
				foreach ($fields as $field) {
					if (isset($settings[$field]) && $settings[$field] === '--TYPOSCRIPT--') {
						$settings[$field] = isset($tsSettings[$field]) ? $tsSettings[$field] : '';
					}
				}
			}

			//looks for TS values for the specified fields, ignoring their flexform value
			if (isset($tsOverride['selectFields'])) {
				$selectFields = t3lib_div::trimExplode(',',$tsOverride['selectFields'],1);
				foreach ($selectFields as $selectField) {
					if (isset($settings[$selectField][0])) {
						$fields = t3lib_div::trimExplode(';',$settings[$selectField],1);
						foreach ($fields as $field) {
							#@SHOULD make this work as overrule-setting, not overwrite
							$settings[$field] = isset($tsSettings[$field]) ? $tsSettings[$field] : '';
						}
					}
				}
			}
		}

		$this->settings = $settings;
	}

	/**
	 * Maps arguments delivered by the request object to the local controller arguments.
	 *
	 * Try and catch construction makes sure a controller argument which no longer exists
	 * in the database, doesn't produce a crash. It catches it, and produces a flashMessage.
	 *
	 * This concerns f.e. an object that was deleted in TCA or a temporary appointment
	 * which has expired, while said object is still being edited in FE.
	 *
	 * @return void
	 */
	protected function mapRequestArgumentsToControllerArguments() {
		try {
			parent::mapRequestArgumentsToControllerArguments();
		} catch (/*Tx_Extbase_MVC_Exception_InvalidArgumentValue*/ Exception $e) {
			#@TODO nalopen wat de exacte voorwaarden zijn voor onderstaande message
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.appointment_create_temp_deleted', $this->extensionName); #@FIXME this needs some serious testing
			$this->flashMessageContainer->add($flashMessage);
			$this->redirect('list');
		}
	}

	/**
	 * A template method for displaying custom error flash messages, or to
	 * display no flash message at all on errors. Override this to customize
	 * the flash message in your action controller.
	 *
	 * @return string|boolean The flash message or FALSE if no flash message should be set
	 */
	protected function getErrorFlashMessage() {
		return FALSE;
	}

}
?>