<?php
namespace Innologi\Appointments\Service;
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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;
/**
 * Facilitates Cross-Site Request Forgery Protection control,
 * implementation for frontend extbase plugins.
 *
 * @package appointments
 * @author Frenck Lutke <typo3@innologi.nl>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Typo3CsrfProtectService extends AbstractCsrfProtectService implements SingletonInterface {

	/**
	 * @var string
	 */
	protected $extConfKey = 'csrf_protection_level';

	/**
	 * The token key name. Note that it starts with double underscore,
	 * which will make the Extbase propertyMapper regard it as an
	 * internal argument to the request. This is important for knowing
	 * how to retrieve it from $request.
	 *
	 * @var string
	 */
	protected $tokenKey = '__stoken';

	/**
	 * @var \TYPO3\CMS\Extbase\Mvc\Web\Request
	 */
	protected $request;

	/**
	 * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
	 * @inject
	 */
	protected $hashService;




	/**
	 * Initializes class properties
	 *
	 * @return void
	 * @see AbstractCsrfProtectService::initialize()
	 */
	protected function initialize() {
		$this->executionTime = (int)$GLOBALS['EXEC_TIME'];
	}





	/**
	 * Provides the csrf-class and encoded uri to a tag for
	 * identification by the JavaScript library.
	 *
	 * @param TagBuilder $tag
	 * @param string $tokenUri
	 * @return void
	 */
	public function provideTagArguments(TagBuilder $tag, $tokenUri = '') {
		$class = array();
		if ($tag->hasAttribute('class')) {
			$class = GeneralUtility::trimExplode(' ', $tag->getAttribute('class'));
		}

		$class[] = $this->jsClass;
		$tag->addAttribute('class', join(' ', $class));

		if (isset($tokenUri[0])) {
			$tag->addAttribute('data-utoken', base64_encode($tokenUri));
		}
	}

	/**
	 * Retrieves URI to base token on.
	 *
	 * Strips cHash and token parameter from URI.
	 *
	 * @return string
	 * @api
	 */
	public function getTokenUri() {
		if ($this->tokenUri === NULL) {
			$this->tokenUri = $this->getRequestUri();

			if ($this->request->getMethod() === 'GET') {
				$this->tokenUri = \Innologi\Appointments\Utility\GeneralUtility::stripGetParameters(
					$this->tokenUri, array(
						'chash',
						urlencode(
							\Innologi\Appointments\Utility\GeneralUtility::wrapGetParameter(
								$this->getTokenKey(),
								$this->request->getControllerExtensionKey(),
								$this->request->getPluginName()
							)
						)
					)
				);
			}
		}
		return $this->tokenUri;
	}
	#@TODO make these two abstract first? from SERVER array?
	/**
	 * Retrieves request uri.
	 *
	 * @return string
	 * @api
	 */
	public function getRequestUri() {
		return $this->request->getRequestUri();
	}

	/**
	 * Retrieves base uri.
	 *
	 * @return string
	 * @api
	 */
	public function getBaseUri() {
		return $this->request->getBaseUri();
	}

	/**
	 * Create token based on uri and a hash.
	 *
	 * @param string $uri
	 * @param string $hash
	 * @return string
	 * @see AbstractCsrfProtectService::getToken()
	 */
	protected function getToken($uri, $hash) {
		return $this->hashService->generateHmac(
			base64_encode($uri) . $hash
		);
	}

	/**
	 * Returns request token. Checks for JS dependency
	 * to determine the token's location.
	 *
	 * @return string
	 * @see AbstractCsrfProtectService::getRequestToken()
	 */
	protected function getRequestToken() {
		return $this->hasJsDependency()
			? parent::getRequestToken()
			: $this->request->getInternalArgument($this->tokenKey);
	}

	/**
	 * Retrieve source for Private Hash. The source is either
	 * a complete URI or the BaseURI, depending on if referrer
	 * dependency is set.
	 *
	 * When a referrer dependency is set, we dont want the order of parameters
	 * to cause a source-mismatch, considering the cache doesnt care either
	 * and wouldnt allow us to generate and store a hash for any variation.
	 * To remedy this, we split and sort the URI by parameters.
	 *
	 * @param boolean $fromReferrer
	 * @return string
	 * @see AbstractCsrfProtectService::getHashSource()
	 */
	protected function getHashSource($fromReferrer = FALSE) {
		if ($this->hasReferrerDependency()) {
			$sourceUri = $fromReferrer || $this->hasJsDependency()
				? $this->getHeader('REFERER')
				: $this->getRequestUri();
			$sourceUri = serialize(
				\Innologi\Appointments\Utility\GeneralUtility::splitUrlAndSortInArray($sourceUri)
			);
		} else {
			$sourceUri = $this->getBaseUri();
		}

		return md5($sourceUri);
	}

	/**
	 * Returns the session data.
	 *
	 * @return array
	 */
	protected function getSessionData() {
		$sessionData = $GLOBALS['TSFE']->fe_user->getKey(
			$this->getSessionDataType(),
			$this->sessionKey
		);
		return is_array($sessionData) ? $sessionData : array();
	}

	/**
	 * Sets the session data.
	 *
	 * @return void
	 */
	protected function setSessionData(array $sessionData) {
		$GLOBALS['TSFE']->fe_user->setKey(
			$this->getSessionDataType(),
			$this->sessionKey,
			$sessionData
		);
		#@TODO _______isnt this just the case because the AJAX request cant seem to access the PHP session? google it, maybe there is a better solution
		// if we don't, retrieval will favor another database stored session
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}





	/**
	 * Sets protection level.
	 *
	 * It has to be done from extConf instead of TS constant,
	 * because it needs to be available in ext_localconf.php and in
	 * the controller's initializeAction()-method. Unfortunately,
	 * this means we can't set it per page.
	 *
	 * If we get rid of the UNCACHED setting, we could move it to a
	 * TS constant, if the need ever arises.
	 *
	 * @param string $extensionKey
	 * @return void
	 */
	public function setProtectionLevelByExtConf($extensionKey) {
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extensionKey])) {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extensionKey]);
			if (isset($extConf[$this->extConfKey])) {
				$this->protectionLevel = (int)$extConf[$this->extConfKey];
			}
		}
	}

	/**
	 * Returns session data type which contains the private hash.
	 *
	 * Can be either 'ses' for token-per-request, or 'user' for token-per-cached-session.
	 *
	 * @return string
	 */
	protected function getSessionDataType() {
		return $this->hasNewTokenPerRequest() ? 'ses' : 'user';
	}

}