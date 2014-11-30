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

	/**
	 * @var array
	 */
	protected $jsDependency = array(
		self::MAXIMUM,
		self::MAXIMUM_PLUS
	);

	/**
	 * @var array
	 */
	protected $referrerDependency = array(
		self::BASIC_PLUS,
		self::STRONG_PLUS,
		self::MAXIMUM_PLUS
	);

	/**
	 * @var array
	 */
	protected $persistedHash = array(
		self::BASIC,
		self::BASIC_PLUS
	);

	/**
	 * @var string
	 */
	protected $jsClass = 'csrf-protect';

	/**
	 * The token key name.
	 *
	 * @var string
	 */
	protected $vendorPrefix = 'innologi';

	/**
	 * The token key name.
	 *
	 * @var string
	 */
	protected $tokenKey = '__stoken';

	/**
	 * Session key name, for internal use only.
	 *
	 * @var string
	 */
	protected $sessionKey = 'innologi__stphcsrf';

	/**
	 * @var string
	 */
	protected $privateHashKey = 'hash';

	/**
	 * Session value Time-To-Live (TTL), in minutes
	 *
	 * @var integer
	 */
	protected $sessionTtl = 20;
	#@TODO configurable ttl?

	/**
	 * @var int
	 */
	protected $executionTime;

	/**
	 * Set to max by default, but should be overruled by implementation.
	 *
	 * @var integer
	 */
	protected $protectionLevel = self::MAXIMUM_PLUS;

	/**
	 * @var string
	 */
	protected $privateHash;

	/**
	 * @var string
	 */
	protected $tokenUri;

	/**
	 * @var object
	 */
	protected $request;





	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->initialize();
	}

	/**
	 * Initializes class properties
	 *
	 * @return void
	 */
	protected function initialize() {
		$this->executionTime = time();
	}





	/**
	 * Checks if the request is allowed after a validity-check.
	 *
	 * @param object $request
	 * @param string $token
	 * @param boolean $neverClearSession
	 * @return boolean
	 * @api
	 */
	public function isRequestAllowed($request, $token = NULL, $neverClearSession = FALSE) {
		$this->request = $request;

		if ($this->validateHeaders()) {
			if ($token === NULL) {
				$token = $this->getRequestToken();
			}
			if ($this->isTokenValid($token)) {
				if (!$neverClearSession && $this->hasNewTokenPerRequest() && $this->hasJsDependency()) {
					// clear session data, otherwise any follow-up request is allowed
					$this->setSessionData(array());
					// @LOW this is inconvenient when in simultaneous sessions, but why would we support that?
				}
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Checks if the Ajax request is allowed after a CSRF-protect-validity-check.
	 *
	 * @param object $request
	 * @return boolean
	 * @api
	 */
	public function isAjaxRequestAllowed($request) {
		$this->privateHashKey = 'ajaxHash';

		// token resides in custom header
		$token = $this->getHeader(
			$this->getTokenHeaderKey()
		);

		// an ajax CSRF-protect request is followed by a normal request, so don't clear session
		$allowed = $this->isRequestAllowed($request, $token, TRUE);
		// reset privateHashKey
		$this->privateHashKey = 'hash';
		return $allowed;
	}

	/**
	 * Generate and return a new token for the $uri.
	 *
	 * @param object $request
	 * @param string $uri
	 * @param boolean $sessionHash
	 * @return string
	 * @api
	 */
	public function generateToken($request, $uri = '', $sessionHash = FALSE) {
		$this->request = $request;

		return $this->getToken(
			$uri,
			$this->getPrivateHash(
				$sessionHash || !$this->hasNewTokenPerRequest()
			)
		);
	}

	/**
	 * Generates and returns 1st-step tokens of js-dependent protection.
	 *
	 * @param object $request
	 * @param array $encodedUrls
	 * @return array
	 * @api
	 */
	public function generateAjaxTokens($request, array $encodedUrls) {
		$tokens = array();

		if (!empty($encodedUrls)) {
			$this->privateHashKey = 'ajaxHash';
			foreach ($encodedUrls as $encodedUrl) {
				$tokens[] = $this->generateToken(
					$request,
					base64_decode($encodedUrl, TRUE)
				);
			}
			// create a normal hash for jsTokens, different from the ajaxHash
			$this->privateHashKey = 'hash';
			$this->createAndStorePrivateHash();
		}

		return $tokens;
	}

	/**
	 * Creates and stores a 2nd-step jsToken for js-dependent protection.
	 *
	 * @param object $request
	 * @param string $uri
	 * @return void
	 * @api
	 */
	public function createAndStoreJsToken($request, $uri) {
		$this->putValueInSession(
			'jsToken',
			$this->generateToken($request, $uri, TRUE)
		);
	}





	/**
	 * Validates headers. Will fail if header doesnt match,
	 * but also if header does not exist or is empty if there is
	 * a referrer depedency.
	 *
	 * @return boolean
	 */
	protected function validateHeaders() {
		$validReferrer = !$this->hasReferrerDependency();

		$headers = array(
			'ORIGIN', // more reliable, apparently
			'REFERER'
		);

		// note that ORIGIN header is usually without trailing slash, so we'll strip it from basUri as well
		$baseUri = rtrim($this->getBaseUri(), '/');

		foreach ($headers as $header) {
			$headerData = $this->getHeader($header);
			if (isset($headerData[0]) && $headerData !== 'null') {
				$validReferrer = strpos($headerData, $baseUri) === 0;
			}
		}

		return $validReferrer;
	}

	/**
	 * Returns request token from the expected location.
	 *
	 * @return string
	 */
	protected function getRequestToken() {
		$requestToken = '';

		if ($this->hasJsDependency()) {
			// resides in session
			$requestToken = $this->getValueFromSession('jsToken', TRUE);
		} else {
			# @LOW what about a proper way of checking if this is a GET or a POST request?
			// resides in request-argument
			$requestToken = isset($_POST[$this->tokenKey]) ? $_POST[$this->tokenKey] : $_GET[$this->tokenKey];
		}

		# @LOW what about security?
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
			$this->getValueFromSession($this->privateHashKey, TRUE)
		);
		return $token;
	}

	/**
	 * Create token based on uri and a hash.
	 *
	 * Note that this function relies on an encryptionkey
	 * whose source is implementation specific. The provided
	 * key '123456' is of course not recommended for actual
	 * use!
	 *
	 * @param string $uri
	 * @param string $hash
	 * @return string
	 */
	protected function getToken($uri, $hash) {
		return hash_hmac('sha1', base64_encode($uri) . $hash, '123456');
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
				$this->privateHash = $this->getValueFromSession($this->privateHashKey);
				if ($this->privateHash !== FALSE) {
					return $this->privateHash;
				}
			}
			$this->createAndStorePrivateHash();
		}
		return $this->privateHash;
	}

	/**
	 * Creates and stores a private hash.
	 *
	 * @return void
	 */
	protected function createAndStorePrivateHash() {
		$this->privateHash = md5(uniqid(rand(), TRUE));
		$this->putValueInSession($this->privateHashKey, $this->privateHash);
	}

	/**
	 * Retrieve source for Private Hash. The source is either
	 * a complete URI or a BaseURI, depending on if referrer
	 * dependency is set.
	 *
	 * @param boolean $fromReferrer
	 * @return string
	 */
	protected function getHashSource($fromReferrer = FALSE) {
		$sourceUri = $this->hasReferrerDependency() ?
			($fromReferrer ? $this->getHeader('REFERER') : $this->getRequestUri()) :
			$this->getBaseUri();
		return md5($sourceUri);
	}




	/**
	 * Gets header keyname for token.
	 *
	 * @return string
	 * @api
	 */
	public function getTokenHeaderKey() {
		return $this->vendorPrefix . $this->tokenKey;
	}

	/**
	 * Gets utoken from header.
	 *
	 * @return string
	 * @api
	 */
	public function getEncodedUrlFromHeader() {
		return $this->getHeader($this->vendorPrefix . '__utoken');
	}

	/**
	 * Gets HTTP header, e.g. REFERER
	 *
	 * @param string $header
	 * @return string
	 */
	protected function getHeader($header) {
		$header = 'HTTP_' . strtoupper($header);
		return isset($_SERVER[$header]) ? $_SERVER[$header] : '';
	}





	/**
	 * Puts value in session.
	 *
	 * STRUCTURE:
	 *
	 * session {
	 *   hashSource {
	 *     time = timestamp
	 *     $key = base64 encoded value
	 *   }
	 * }
	 *
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	protected function putValueInSession($key, $value) {
		$data = array(
			'time' => time(),
			$key => base64_encode($value),
		);

		$hashSource = $this->getHashSource();
		$sessionData = $this->getSessionData();
		// we allow merging, to allow different keys for different purposes to co-exist
		$sessionData[$hashSource] = isset($sessionData[$hashSource])
			? array_merge($sessionData[$hashSource], $data)
			: $data;
		$this->setSessionData($sessionData);
	}

	/**
	 * Retrieves value from session, or boolean false on failure.
	 *
	 * @param string $key
	 * @param boolean $generatedByReferrer
	 * @return string
	 */
	protected function getValueFromSession($key, $generatedByReferrer = FALSE) {
		$sessionData = $this->getSessionData();
		$hashSource = $this->getHashSource($generatedByReferrer);

		$value = FALSE;
		if (isset($sessionData[$hashSource]) && $this->isSessionValueStillValid($sessionData[$hashSource])) {
			$value = base64_decode($sessionData[$hashSource][$key]);
		}

		return $value;
	}

	/**
	 * Checks if the given session value is valid to use.
	 *
	 * Looks at $this->sessionTtl to determine the allowed lifetime.
	 * So if this is set to 20 minutes, and the session value was produced
	 * at 1-1-2015 19:40:00, the hash would turn invalid at
	 * 1-1-2015 20:00:00.
	 *
	 * The exception to this rule is when the token using this session value
	 * is both not per-request and not referrer-dependent. This is
	 * because a token not per-request is subjective to being cached,
	 * while a token not referrer-dependent will rely on the same private
	 * hash for every page. Consider with the above example, that when
	 * the TTL has passed, a second (uncached) page will regenerate the
	 * session value, while the first page keeps its now invalid values cached.
	 *
	 * Hence, the TTL check is ignored in the given situation.
	 * Note however, that the session data is persisted in the given
	 * situation. If utilized for a private hash, the user would then
	 * only ever receive 1 private hash..
	 *
	 * @param array $sessionValue Session Value as mapped to hashSource in session
	 * @return boolean
	 */
	protected function isSessionValueStillValid(array $sessionValue) {
		return (!$this->hasNewTokenPerRequest() && !$this->hasReferrerDependency())
			|| (($this->executionTime - $sessionValue['time']) < ($this->sessionTtl * 60));
	}

	/**
	 * Returns the session data.
	 *
	 * @return array
	 */
	protected function getSessionData() {
		return isset($_SESSION[$this->sessionKey]) ? $_SESSION[$this->sessionKey] : array();
	}

	/**
	 * Sets session data
	 *
	 * @param array $sessionData
	 * @return void
	 */
	protected function setSessionData(array $sessionData) {
		$_SESSION[$this->sessionKey] = $sessionData;
	}





	/**
	 * Sets token Uri
	 * @param string $tokenUri
	 * @return void
	 * @api
	 */
	public function setTokenUri($tokenUri) {
		$this->tokenUri = $tokenUri;
	}

	/**
	 * Get token key-name.
	 *
	 * @return string
	 * @api
	 */
	public function getTokenKey() {
		return $this->tokenKey;
	}

	/**
	 * Returns whether the service is enabled.
	 *
	 * @return boolean
	 * @api
	 */
	public function isEnabled() {
		return $this->protectionLevel !== self::DISABLED;
	}

	/**
	 * Returns whether the service is set to depend
	 * on JavaScript mechanisms.
	 *
	 * @return boolean
	 * @api
	 */
	public function hasJsDependency() {
		return in_array($this->protectionLevel, $this->jsDependency, TRUE);
	}

	/**
	 * Returns whether the service is set to depend
	 * on the existence of a referrer header.
	 *
	 * @return boolean
	 * @api
	 */
	public function hasReferrerDependency() {
		return in_array($this->protectionLevel, $this->referrerDependency, TRUE);
	}

	/**
	 * Returns whether the service is set to use a new token
	 * per request, as opposed to per session.
	 *
	 * @return boolean
	 * @api
	 */
	public function hasNewTokenPerRequest() {
		return !in_array($this->protectionLevel, $this->persistedHash, TRUE);
	}

}
?>