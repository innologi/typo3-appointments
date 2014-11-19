<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * CSRF-Protect Controller
 *
 * Implements the CSRF-Protect Service and adds support for the
 * @verifycsrftoken annotation as switch to enable the protection
 * per method-action.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Appointments_MVC_Controller_CsrfProtectController extends Tx_Appointments_MVC_Controller_SettingsOverrideController {

	/**
	 * Enables checking for the method-annotations
	 *
	 * Can be overridden by extending domain controllers
	 *
	 * @var boolean
	 */
	protected $enableCsrfProtect = TRUE;

	/**
	 * @var Tx_Appointments_Service_CsrfProtectService
	 */
	protected $csrfProtectService;

	/**
	 * @param Tx_Appointments_Service_CsrfProtectService $csrfProtectService
	 * @return void
	 */
	public function injectCsrfProtectService(Tx_Appointments_Service_CsrfProtectService $csrfProtectService) {
		// note that strtolower wouldnt suffice in case of underscores in extension key
		$csrfProtectService->setProtectionLevelByExtConf(strtolower($this->extensionName));
		$this->csrfProtectService = $csrfProtectService;
	}

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * Performs the CSRF-protect mechanisms on current action ONLY if enabled in controller,
	 * through configuration and through the method's @verifycsrftoken annotation.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		if ($this->enableCsrfProtect && $this->csrfProtectService->isEnabled()) {
			// check if method has @verifycsrftoken annotation
			$methodTagsValues = $this->reflectionService->getMethodTagsValues(get_class($this), $this->actionMethodName);
			if (isset($methodTagsValues['verifycsrftoken'])) {
				// is request not a potential CSRF-attempt?
				if (!$this->csrfProtectService->isRequestAllowed($this->request)) {
					#@FIX ______probably need to throw exceptions in service, and catch here to produce nice flash messages
					#@TODO _____relevancy!
					throw new Exception('Appointments: CSRF token error');
				}
				#die('<html><head><title>SUCCESS!</title></head><body><p>success!</p></body></html>');
			}
		}
	}

	/**
	 *
	 *
	 * @param string $encodedUrl
	 * @return void
	 */
	public function generateTokenAction($encodedUrl) {
		$token = $this->csrfProtectService->generateToken($this->request, base64_decode($encodedUrl, TRUE), TRUE);
		$this->response->setHeader('typo3-appointments__stoken', $token);
		$this->response->sendHeaders();
		exit;
	}

	/**
	 *
	 *
	 * @param string $encodedUrl
	 * @return void
	 */
	public function forceNewPrivateHashAction() {
		$this->csrfProtectService->forceNewPrivateHash($this->request);
		exit;
	}

}
?>