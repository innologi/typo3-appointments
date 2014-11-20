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
 * Link Action ViewHelper
 *
 * Takes the original and adds in the boolean attribute 'syncToken',
 * which if set to TRUE, will let the link generate a synchronizer-token
 * for CSRF-protection.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Appointments_ViewHelpers_Link_ActionViewHelper extends Tx_Fluid_ViewHelpers_Link_ActionViewHelper {
	#@TODO _______ use DI override instead once we lose 4.x compatibility

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
	 * @param string $action Target action
	 * @param array $arguments Arguments
	 * @param string $controller Target controller. If NULL current controllerName is used
	 * @param string $extensionName Target Extension Name (without "tx_" prefix and no underscores). If NULL the current extension name is used
	 * @param string $pluginName Target plugin. If empty, the current plugin name is used
	 * @param integer $pageUid target page. See TypoLink destination
	 * @param integer $pageType type of the target page. See typolink.parameter
	 * @param boolean $noCache set this to disable caching for the target page. You should not need this.
	 * @param boolean $noCacheHash set this to supress the cHash query parameter created by TypoLink. You should not need this.
	 * @param string $section the anchor to be added to the URI
	 * @param string $format The requested format, e.g. ".html
	 * @param boolean $linkAccessRestrictedPages If set, links pointing to access restricted pages will still link to the page even though the page cannot be accessed.
	 * @param array $additionalParams additional query parameters that won't be prefixed like $arguments (overrule $arguments)
	 * @param boolean $absolute If set, the URI of the rendered link is absolute
	 * @param boolean $addQueryString If set, the current query parameters will be kept in the URI
	 * @param array $argumentsToBeExcludedFromQueryString arguments to be removed from the URI. Only active if $addQueryString = TRUE
	 * @param string $addQueryStringMethod Set which parameters will be kept. Only active if $addQueryString = TRUE
	 * @return string Rendered link
	 */
	public function render($action = NULL, array $arguments = array(), $controller = NULL, $extensionName = NULL, $pluginName = NULL, $pageUid = NULL, $pageType = 0, $noCache = FALSE, $noCacheHash = FALSE, $section = '', $format = '', $linkAccessRestrictedPages = FALSE, array $additionalParams = array(), $absolute = FALSE, $addQueryString = FALSE, array $argumentsToBeExcludedFromQueryString = array(), $addQueryStringMethod = NULL) {
		if (isset($this->arguments['syncToken']) && $this->arguments['syncToken'] === TRUE && $this->csrfProtectService->isEnabled()) {
			// we have to build a variation of the URI that is
			// absolute and without a cHash, for use as tokenUri
			$uriBuilder = $this->controllerContext->getUriBuilder();
			$tokenUri = $uriBuilder->reset()
				->setTargetPageUid($pageUid)
				->setTargetPageType($pageType)
				->setNoCache($noCache)
				->setUseCacheHash(FALSE)
				->setSection($section)
				->setFormat($format)
				->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
				->setArguments($additionalParams)
				->setCreateAbsoluteUri(TRUE)
				->setAddQueryString($addQueryString)
				->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString)
				->setAddQueryStringMethod($addQueryStringMethod)
				->uriFor($action, $arguments, $controller, $extensionName, $pluginName);

			if ($this->csrfProtectService->hasJsDependency()) {
				$this->csrfProtectService->provideTagArguments($this->tag, $tokenUri);
			} else {
				$arguments[$this->csrfProtectService->getTokenKey()] = htmlspecialchars(
					$this->csrfProtectService->generateToken(
						$this->controllerContext->getRequest(), $tokenUri
					)
				);
			}
		}

		return parent::render(
			$action, $arguments, $controller, $extensionName, $pluginName,
			$pageUid, $pageType, $noCache, $noCacheHash, $section, $format,
			$linkAccessRestrictedPages, $additionalParams, $absolute, $addQueryString,
			$argumentsToBeExcludedFromQueryString, $addQueryStringMethod
		);
	}
}
?>