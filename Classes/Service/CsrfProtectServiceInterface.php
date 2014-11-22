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
 * Interface for Cross-Site Request Forgery Protection service.
 *
 * @package appointments
 * @author Frenck Lutke <frenck@innologi.nl>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface Tx_Appointments_Service_CsrfProtectServiceInterface {

	/*
	 * These constants represent the methods of protection, each with its own trade-off.
	 *
	 * All tokens are per user and per uri.
	 *
	 * - The PLUS methods are all referrer-dependent
	 * - The MAXIMUM methods are all JavaScript-dependent
	 * - The STRONG methods all give up caching
	 */

	// no token
	const DISABLED = 0;
	// token (permanent hash)
	const BASIC = 1;
	// token per cache per page
	const BASIC_PLUS = 2;
	// token per request
	const STRONG = 3;
	// token per request per page
	const STRONG_PLUS = 4;
	// token per request
	const MAXIMUM = 5;
	// token per request per page
	const MAXIMUM_PLUS = 6;

	/**
	 * Checks if the request is allowed after a CSRF-protect-validity-check.
	 *
	 * @param Tx_Extbase_MVC_Web_Request $request
	 * @return boolean
	 */
	public function isRequestAllowed(Tx_Extbase_MVC_Web_Request $request);

	/**
	 * Generate and return a new token for the $uri.
	 *
	 * @param Tx_Extbase_MVC_Web_Request $request
	 * @param string $uri
	 * @param boolean $useSessionHash
	 * @return string
	 */
	public function generateToken(Tx_Extbase_MVC_Web_Request $request, $uri = '', $useSessionHash = FALSE);

	/**
	 * Force the creation of a new private hash.
	 *
	 * @param Tx_Extbase_MVC_Web_Request $request
	 * @return void
	 */
	public function forceNewPrivateHash(Tx_Extbase_MVC_Web_Request $request);

	/**
	 * Provides the csrf-class and encoded uri to a tag for
	 * identification by the JavaScript library.
	 *
	 * @param Tx_Fluid_Core_ViewHelper_TagBuilder $tag
	 * @param string $tokenUri
	 * @return void
	 */
	public function provideTagArguments(Tx_Fluid_Core_ViewHelper_TagBuilder $tag, $tokenUri = '');

	/**
	 * Get token key-name.
	 *
	 * @return string
	 */
	public function getTokenKey();

	/**
	 * Returns whether the service is enabled.
	 *
	 * @return boolean
	 */
	public function isEnabled();

	/**
	 * Returns whether the service is set to depend
	 * on JavaScript mechanisms.
	 *
	 * @return boolean
	 */
	public function hasJsDependency();

	/**
	 * Returns whether the service is set to depend
	 * on the existence of e.g. a referrer header.
	 *
	 * @return boolean
	 */
	public function hasHeaderDependency();

	/**
	 * Returns whether the service is set to use a new token
	 * per request, as opposed to per session.
	 *
	 * @return boolean
	 */
	public function hasNewTokenPerRequest();

}
?>