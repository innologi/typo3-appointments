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
	protected $headerDependency = array(
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
	protected $tokenKey = '__stoken';

	/**
	 * Session key name, for internal use only.
	 *
	 * @var string
	 */
	protected $sessionKey = '__innologi_stphcsrf';

	/**
	 * Hash time to live, in minutes
	 *
	 * @var integer
	 */
	protected $hashTtl = 20;
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
	 * @return boolean
	 * @api
	 */
	public function isRequestAllowed($request) {
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
	 * @param object $request
	 * @param string $uri
	 * @param boolean $useSessionHash
	 * @return string
	 * @api
	 */
	public function generateToken($request, $uri = '', $useSessionHash = FALSE) {
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
	 * @param object $request
	 * @return void
	 * @api
	 */
	public function forceNewPrivateHash($request) {
		$this->request = $request;
		#@FIX think about the structure applied here.. isn't this completely pointless?
		$this->generateNewPrivateHash();
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

		// note that ORIGIN header is usually without trailing slash, so we'll strip it from basUri as well
		$baseUri = rtrim($this->getBaseUri(), '/');

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
			$tokenName = 'Innologi' . $this->tokenKey;
			$headerName = 'HTTP_' . strtoupper($tokenName);
			if (isset($_SERVER[$headerName])) {
				$requestToken = $_SERVER[$headerName];
			} else {
				// @TODO _______relevancy!
				#throw new Exception('NOJS!');
			}
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
			$this->getPrivateHashFromSession(TRUE)
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
	 * Retrieves private hash from session, or boolean false on failure.
	 *
	 * @param boolean $generatedByReferrer
	 * @return string
	 */
	protected function getPrivateHashFromSession($generatedByReferrer = FALSE) {
		$sessionData = $this->getSessionData();
		$hashSource = $this->getHashSource($generatedByReferrer);

		// false hash will always produce invalid outcome
		$privateHash = FALSE;
		if (isset($sessionData[$hashSource]) && $this->isHashStillValid($sessionData[$hashSource])) {
			$privateHash = base64_decode($sessionData[$hashSource]['hash']);
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
				if ($this->privateHash !== FALSE) {
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
	 * STRUCTURE:
	 *
	 * session {
	 *   hashSource {
	 *     time = timestamp
	 *     timedHashKey = base64 encoded private hash
	 *   }
	 * }
	 *
	 * @param string $privateHash
	 * @return void
	 */
	protected function putPrivateHashInSession($privateHash) {
		$data = array(
			'time' => time(),
			'hash' => base64_encode($privateHash),
		);

		$sessionData = $this->getSessionData();
		$sessionData[$this->getHashSource()] = $data;
		$this->setSessionData($sessionData);
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
			($fromReferrer ? $this->getReferrer() : $this->getRequestUri()) :
			$this->getBaseUri();
		return md5($sourceUri);
	}

	/**
	 * Checks if the given hash is valid to use.
	 *
	 * Looks at $this->hashTtl to determine the allowed interval.
	 * So if this is set to 20 minutes, and the hash was produced
	 * at 1-1-2015 19:40:00, the hash would turn invalid at
	 * 1-1-2015 20:00:00.
	 *
	 * The exception to this rule is when the token using this hash
	 * is both not per-request and not header-dependent. This is
	 * because a token not per-request is subjective to being cached,
	 * while a token not header-dependent will rely on the same private
	 * hash for every page. Consider with the above example, that when
	 * the TTL has passed, a second (uncached) page will regenerate the
	 * hash, while the first page keeps its now invalid tokens cached.
	 *
	 * Hence, the TTL check is ignored in the given situation.
	 * Note however, that the session data is persisted in the given
	 * situation, so a user will only ever receive 1 private hash..
	 *
	 * @param array $hash HashData as mapped to hashSource in session
	 * @return boolean
	 */
	protected function isHashStillValid(array $hash) {
		return (!$this->hasNewTokenPerRequest() && !$this->hasHeaderDependency())
			|| (($this->executionTime - $hash['time']) < ($this->hashTtl * 60));
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
		return !in_array($this->protectionLevel, $this->persistedHash, TRUE);
	}

}
?>