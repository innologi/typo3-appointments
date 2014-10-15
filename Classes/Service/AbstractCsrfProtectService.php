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
 * Facilitates Cross-Site Request Forgery Protection control, abstract implementation.
 *
 * @package appointments
 * @author Frenck Lutke <frenck@innologi.nl>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class Tx_Appointments_Service_AbstractCsrfProtectService implements Tx_Appointments_Service_CsrfProtectServiceInterface {

	const DISABLED = 0;
	const BASIC = 1;
	// in order for UNCACHED to work, the implementation needs to check for this value when it defines cached actions
	const STRONG_UNCACHED = 2;
	const STRONG_HEADER = 3;
	const STRONG_JS = 4;
	const MAXIMUM = 5;

	/**
	 * @var array
	 */
	protected $jsDependency = array(
		self::STRONG_JS,
		self::MAXIMUM
	);

	/**
	 * @var array
	 */
	protected $headerDependency = array(
		self::STRONG_HEADER,
		self::MAXIMUM
	);

	/**
	 * @var string
	 */
	protected $jsClass = 'csrf-protect';

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
	 * Session key name, for internal use only.
	 *
	 * @var string
	 */
	protected $sessionKey = '__stphcsrf';

	/**
	 * Set to max by default, but should be overruled by implementation.
	 *
	 * @var integer
	 */
	protected $protectionLevel = self::MAXIMUM;

	/**
	 * @var string
	 */
	protected $privateHash;

	/**
	 * @var Tx_Extbase_MVC_Web_Request $request
	 */
	protected $request;

	/**
	 * @var Tx_Extbase_Security_Cryptography_HashService
	 */
	protected $hashService;

	/**
	 * @param Tx_Extbase_Security_Cryptography_HashService $hashService
	 * @return void
	 */
	public function injectHashService(Tx_Extbase_Security_Cryptography_HashService $hashService) {
		$this->hashService = $hashService;
	}





	/**
	 * Checks if the request is allowed after a validity-check.
	 *
	 * @param Tx_Extbase_MVC_Web_Request $request
	 * @return boolean
	 */
	public function isRequestAllowed(Tx_Extbase_MVC_Web_Request $request) {
		$this->request = $request;

		if ($this->validateHeaders()) {
			$token = $this->getRequestToken();
			if ($this->isTokenValid($token)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Generate and return a new token for the $uri.
	 *
	 * @param Tx_Extbase_MVC_Web_Request $request
	 * @param string $uri
	 * @param boolean $useSessionHash
	 * @return string
	 */
	public function generateToken(Tx_Extbase_MVC_Web_Request $request, $uri = '', $useSessionHash = FALSE) {
		$this->request = $request;

		return $this->getToken(
			$uri,
			$this->getPrivateHash(
				$useSessionHash || !$this->hasNewTokenPerRequest()
			)
		);
	}

	/**
	 * Force the creation of a new private hash.
	 *
	 * @param Tx_Extbase_MVC_Web_Request $request
	 * @return void
	 */
	public function forceNewPrivateHash(Tx_Extbase_MVC_Web_Request $request) {
		$this->request = $request;

		$this->generateNewPrivateHash();
	}

	/**
	 * Provides the csrf-class and encoded uri to a tag for
	 * identification by the JavaScript library.
	 *
	 * @param Tx_Fluid_Core_ViewHelper_TagBuilder $tag
	 * @param string $tokenUri
	 * @return void
	 */
	public function provideTagArguments(Tx_Fluid_Core_ViewHelper_TagBuilder $tag, $tokenUri = '') {
		$class = array();
		if ($tag->hasAttribute('class')) {
			$class = t3lib_div::trimExplode(' ', $tag->getAttribute('class'));
		}

		$class[] = $this->jsClass;
		$tag->addAttribute('class', join(' ', $class));

		if (isset($tokenUri[0])) {
			$tag->addAttribute('data-stoken', base64_encode($tokenUri));
		}
	}





	/**
	 * Validates headers. Will fail if header doesnt match,
	 * but also if header does not exist or is empty if there is
	 * a header depedency.
	 *
	 * @return boolean
	 */
	protected function validateHeaders() {
		$validReferrer = !$this->hasHeaderDependency();

		$headers = array(
			'HTTP_ORIGIN', // more reliable, apparently
			'HTTP_REFERER'
		);

		$baseUri = $this->request->getBaseURI();

		foreach ($headers as $header) {
			if (isset($_SERVER[$header])) {
				$headerData = $_SERVER[$header];
				if (isset($headerData[0]) && $headerData !== 'null') {
					$validReferrer = strpos($headerData, $baseUri) === 0;
					break;
				}
			}
		}

		return $validReferrer;
	}

	/**
	 * Returns request token. Checks for JS dependency
	 * to determine the token's location.
	 *
	 * @return string
	 */
	protected function getRequestToken() {
		$requestToken = '';

		if ($this->hasJsDependency()) {
			// resides in custom header
			$tokenName = 'TYPO3_' . $this->request->getControllerExtensionKey() . $this->tokenKey;
			$headerName = 'HTTP_' . strtoupper($tokenName);
			if (isset($_SERVER[$headerName])) {
				$requestToken = $_SERVER[$headerName];
			} else {
				// @TODO _______relevancy!
				#throw new Exception('NOJS!');
			}
		} else {
			// resides in request-argument
			$requestToken = $this->request->getInternalArgument($this->tokenKey);
		}

		return $requestToken;
	}

	/**
	 * Returns whether the given token is valid.
	 *
	 * @param string $token
	 * @return boolean
	 */
	protected function isTokenValid($token) {
		return isset($token[0]) && $token === $this->getExpectedToken();
	}

	/**
	 * Returns the expected token based on the
	 * request-URI and session-stored hash.
	 *
	 * @return string
	 */
	protected function getExpectedToken() {
		$token = $this->getToken(
			$this->getTokenUri(),
			$this->getPrivateHashFromSession(TRUE)
		);
		return $token;
	}

	/**
	 * Create token based on uri and a hash.
	 *
	 * @param string $uri
	 * @param string $hash
	 * @return string
	 */
	protected function getToken($uri, $hash) {
		return $this->hashService->generateHmac(
			base64_encode($uri) . $hash
		);
	}

	/**
	 * Retrieves uri to base token on.
	 *
	 * @return string
	 */
	protected function getTokenUri() {
		return $this->request->getRequestURI();
	}
	#@TODO timelimit per hash?
	/**
	 * Retrieves private hash from session, or boolean false on failure.
	 *
	 * @param boolean $generatedByReferrer
	 * @return string
	 */
	protected function getPrivateHashFromSession($generatedByReferrer = FALSE) {
		// false hash will always produce invalid outcome
		$privateHash = FALSE;
		$hashSource = $this->getHashSource($generatedByReferrer);
		if (isset($_SESSION[$this->sessionKey]['__h'][$hashSource])) {
			$privateHash = base64_decode($_SESSION[$this->sessionKey]['__h'][$hashSource]);
		}
		return $privateHash;
	}

	/**
	 * Get private hash, or create new one if non-existent. Optionally,
	 * you can let it search for an earlier-stored hash.
	 *
	 * @param boolean $storedHash
	 * @return string
	 */
	protected function getPrivateHash($storedHash = FALSE) {
		if ($this->privateHash === NULL) {
			if ($storedHash) {
				$this->privateHash = $this->getPrivateHashFromSession();
				if ($this->pivateHash !== FALSE) {
					return $this->privateHash;
				}
			}
			$this->generateNewPrivateHash();
		}
		return $this->privateHash;
	}

	/**
	 * Generates a new private hash and stores it in the session
	 *
	 * @return void
	 */
	protected function generateNewPrivateHash() {
		$this->privateHash = md5(uniqid(rand(), TRUE));
		$this->putPrivateHashInSession($this->privateHash);
	}

	/**
	 * Puts private hash in session, and optionally persists the session data.
	 *
	 * @param string $privateHash
	 * @return void
	 */
	protected function putPrivateHashInSession($privateHash) {
		$hashSource = $this->getHashSource();

		if (isset($_SESSION[$this->sessionKey]['__h'])) {
			$_SESSION[$this->sessionKey]['__h'][$hashSource] = base64_encode($privateHash);
		} else {
			$_SESSION[$this->sessionKey] = array(
				'__h' => array(
					$hashSource => base64_encode($privateHash)
				)
			);
		}
	}

	/**
	 * Retrieves referrer from header.
	 *
	 * @return string
	 */
	protected function getReferrer() {
		$referrer = '';
		$header = 'HTTP_REFERER';
		if (isset($_SERVER[$header])) {
			$referrer = $_SERVER[$header];
		} else {
			// @TODO ________ throw exception?
		}
		return $referrer;
	}

	/**
	 * Retrieve source for Private Hash. The source is either
	 * a complete URI or a BaseURI, depending on if header
	 * dependency is set.
	 *
	 * @param boolean $fromReferrer
	 * @return string
	 */
	protected function getHashSource($fromReferrer = FALSE) {
		$sourceUri = $this->hasHeaderDependency() ?
			($fromReferrer ? $this->getReferrer() : $this->request->getRequestURI()) :
			$this->request->getBaseURI();
		return md5($sourceUri);
	}





	/**
	 * Get token key-name.
	 *
	 * @return string
	 */
	public function getTokenKey() {
		return $this->tokenKey;
	}

	/**
	 * Returns whether the service is enabled.
	 *
	 * @return boolean
	 */
	public function isEnabled() {
		return $this->protectionLevel !== self::DISABLED;
	}

	/**
	 * Returns whether the service is set to depend
	 * on JavaScript mechanisms.
	 *
	 * @return boolean
	 */
	public function hasJsDependency() {
		return in_array($this->protectionLevel, $this->jsDependency, TRUE);
	}

	/**
	 * Returns whether the service is set to depend
	 * on the existence of e.g. a referrer header.
	 *
	 * @return boolean
	 */
	public function hasHeaderDependency() {
		return in_array($this->protectionLevel, $this->headerDependency, TRUE);
	}

	/**
	 * Returns whether the service is set to use a new token
	 * per request, as opposed to per session.
	 *
	 * @return boolean
	 */
	public function hasNewTokenPerRequest() {
		return $this->protectionLevel !== self::BASIC;
	}

}
?>