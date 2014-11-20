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
 * Facilitates Cross-Site Request Forgery Protection control,
 * implementation for frontend extbase plugins.
 *
 * @package appointments
 * @author Frenck Lutke <frenck@innologi.nl>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Appointments_Service_Typo3CsrfProtectService extends Tx_Appointments_Service_AbstractCsrfProtectService implements t3lib_Singleton {

	/**
	 * @var string
	 */
	protected $extConfKey = 'csrf_protection_level';

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
	 * Retrieves URI to base token on.
	 *
	 * Strips cHash and token parameter from URI.
	 *
	 * @return string
	 * @see Tx_Appointments_Service_AbstractCsrfProtectService::getTokenUri()
	 */
	protected function getTokenUri() {
		$tokenUri = parent::getTokenUri();

		if ($this->request->getMethod() === 'GET') {
			$tokenUri = Tx_Appointments_Utility_GeneralUtility::stripGetParameters(
				$tokenUri, array(
					'chash',
					urlencode(
						Tx_Appointments_Utility_GeneralUtility::wrapGetParameter(
							$this->getTokenKey(),
							$this->request->getControllerExtensionKey(),
							$this->request->getPluginName()
						)
					)
				)
			);
		}
		return $tokenUri;
	}

	/**
	 * Retrieves private hash from session, or boolean false on failure.
	 *
	 * @param boolean $generatedByReferrer
	 * @return string
	 * @see Tx_Appointments_Service_AbstractCsrfProtectService::getPrivateHashFromSession()
	 */
	protected function getPrivateHashFromSession($generatedByReferrer = FALSE) {
		// false hash will always produce invalid outcome
		$privateHash = FALSE;
		$sessionKey = $this->request->getControllerExtensionKey() . $this->sessionKey;
		$sessionData = $GLOBALS['TSFE']->fe_user->getKey('user', $sessionKey);
		$hashSource = $this->getHashSource($generatedByReferrer);
		if (isset($sessionData['__h'][$hashSource])) {
			$privateHash = base64_decode($sessionData['__h'][$hashSource], TRUE);
		}
		return $privateHash;
	}

	/**
	 * Puts private hash in persisted(!) user session.
	 *
	 * @param string $privateHash
	 * @return void
	 * @see Tx_Appointments_Service_AbstractCsrfProtectService::putPrivateHashInSession()
	 */
	protected function putPrivateHashInSession($privateHash) {
		$sessionKey = $this->request->getControllerExtensionKey() . $this->sessionKey;
		$sessionData = $GLOBALS['TSFE']->fe_user->getKey('user', $sessionKey);
		$hashSource = $this->getHashSource();

		if (isset($sessionData['__h'])) {
			$sessionData['__h'][$hashSource] = base64_encode($privateHash);
		} else {
			$sessionData = array(
				'__h' => array(
					$hashSource => base64_encode($privateHash)
				)
			);
		}
		$GLOBALS['TSFE']->fe_user->setKey('user', $sessionKey, $sessionData);
		// if we don't, retrieval will favor another database stored session
		#@TODO _______isnt this just the case because the AJAX request cant seem to access the PHP session? google it, maybe there is a better solution
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}


	/**
	 * Retrieve source for Private Hash. The source is either
	 * a complete URI or the BaseURI, depending on if header
	 * dependency is set.
	 *
	 * When a header dependency is set, we dont want the order of parameters
	 * to cause a source-mismatch, considering the cache doesnt care either
	 * and wouldnt allow us to generate and store a hash for any variation.
	 * To remedy this, we split and sort the URI by parameters.
	 *
	 * @param boolean $fromReferrer
	 * @return string
	 * @see Tx_Appointments_Service_AbstractCsrfProtectService::getHashSource()
	 */
	protected function getHashSource($fromReferrer = FALSE) {
		if ($this->hasHeaderDependency()) {
			$sourceUri = $fromReferrer ? $this->getReferrer() : $this->request->getRequestURI();
			$sourceUri = serialize(
				Tx_Appointments_Utility_GeneralUtility::splitUrlAndSortInArray($sourceUri)
			);
		} else {
			$sourceUri = $this->request->getBaseURI();
		}

		return md5($sourceUri);
	}
}
?>