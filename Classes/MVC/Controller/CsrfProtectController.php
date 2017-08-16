<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Core\Messaging\FlashMessage;
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

	// @LOW review public/protected in service
	// @LOW pull apart service into different files?
	// @LOW put the csrf protection lib into a separate lib ext?
	// @LOW make implementation as easy as some TS (see streamovations_vp REST lib)

	/**
	 * Enables checking for the method-annotations
	 *
	 * Can be overridden by extending domain controllers
	 *
	 * @var boolean
	 */
	protected $enableCsrfProtect = TRUE;

	/**
	 * @var Tx_Appointments_Service_CsrfProtectServiceInterface
	 */
	protected $csrfProtectService;

	/**
	 * @param Tx_Appointments_Service_CsrfProtectServiceInterface $csrfProtectService
	 * @return void
	 */
	public function injectCsrfProtectService(Tx_Appointments_Service_CsrfProtectServiceInterface $csrfProtectService) {
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
					#@LOW consider throwing exceptions in service, and catching them here to produce relevant error messages
					$flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_appointments.csrf_invalid_request', $this->extensionName);
					// necessary to use flash messages, but not built until after initializeAction()
					$this->controllerContext = $this->buildControllerContext();
					$this->addFlashMessage($flashMessage, '', FlashMessage::ERROR);
					if ($this->request->getInternalArgument('__referrer') !== NULL) {
						// forms can use this to get back to the original form
						$this->forward('error');
					} else {
						$this->clearCacheOnError();
						// @LOW this should detect the default action I guess
						$this->redirect('list');
					}
				}
				#die('<html><head><title>SUCCESS!</title></head><body><p>success!</p></body></html>');
			}
		}
	}

	/**
	 * Verifies token and if passed, creates a valid jsToken via Ajax request
	 *
	 * @param string $encodedUrl
	 * @return void
	 */
	public function ajaxVerifyTokenAction($encodedUrl) {
		$tokenUri = base64_decode($encodedUrl, TRUE);
		$this->csrfProtectService->setTokenUri($tokenUri);

		if ($this->csrfProtectService->isAjaxRequestAllowed($this->request)) {
			$this->csrfProtectService->createAndStoreJsToken($this->request, $tokenUri);
		}
		exit;
	}

	/**
	 * Generate Tokens via Ajax request
	 *
	 * @return void
	 */
	public function ajaxGenerateTokensAction() {
		$encodedUrls = $this->csrfProtectService->getEncodedUrlFromHeader();
		if (isset($encodedUrls[0])) {
			$this->response->setHeader(
				$this->csrfProtectService->getTokenHeaderKey(),
				join(',', $this->csrfProtectService->generateAjaxTokens(
					$this->request,
					explode(',', $encodedUrls)
				))
			);
			$this->response->sendHeaders();
		}
		exit;
	}

}
?>