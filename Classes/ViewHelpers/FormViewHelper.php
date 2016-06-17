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

/**
 * Form ViewHelper
 *
 * Takes the original and adds in the boolean attribute 'syncToken',
 * which if set to TRUE, will let the form generate a synchronizer-token
 * for CSRF-protection.
 *
 * Considering it is a hidden field with a relation to the referrer,
 * I placed the modification in renderHiddenReferrerFields(), with the
 * added benefit of overruling an argument-less class.
 *
 * Note that you also need to set absolute=TRUE for 'syncToken' to work. This
 * isnt done automatically in this version, as opposed to in LinkViewHelper.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Appointments_ViewHelpers_FormViewHelper extends Tx_Fluid_ViewHelpers_FormViewHelper {

	/**
	 * @var string
	 */
	protected $extensionKey = 'appointments';

	/**
	 * @var Tx_Appointments_Service_CsrfProtectServiceInterface
	 */
	protected $csrfProtectService;

	/**
	 * @param Tx_Appointments_Service_CsrfProtectServiceInterface $csrfProtectService
	 * @return void
	 */
	public function injectCsrfProtectService(Tx_Appointments_Service_CsrfProtectServiceInterface $csrfProtectService) {
		$csrfProtectService->setProtectionLevelByExtConf($this->extensionKey);
		$this->csrfProtectService = $csrfProtectService;
	}

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('syncToken', 'boolean', 'If TRUE, adds token for CSRF protection.');
	}

	/**
	 * Renders hidden form fields for referrer information about
	 * the current controller and action.
	 *
	 * @return string Hidden fields with referrer information
	 */
	protected function renderHiddenReferrerFields() {
		$result = parent::renderHiddenReferrerFields();

		if (isset($this->arguments['syncToken']) && ((bool)$this->arguments['syncToken']) === TRUE && $this->csrfProtectService->isEnabled()) {
			$tokenUri = htmlspecialchars_decode($this->tag->getAttribute('action'));

			if ($this->csrfProtectService->hasJsDependency()) {
				$this->csrfProtectService->provideTagArguments($this->tag, $tokenUri);
			} else {
				// @LOW check if adding it to arguments instead would suffice
				$result .= '<input type="hidden" name="' . $this->prefixFieldName(
					$this->csrfProtectService->getTokenKey()
				) . '" value="' . htmlspecialchars(
					$this->csrfProtectService->generateToken(
						$this->controllerContext->getRequest(), $tokenUri
					)
				) . '" />';
			}
		}
		return $result;

	}
}
?>