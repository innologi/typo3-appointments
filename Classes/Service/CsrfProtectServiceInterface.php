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
 * Interface for Cross-Site Request Forgery Protection service.
 *
 * @package appointments
 * @author Frenck Lutke <typo3@innologi.nl>
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
	 * @param object $request
	 * @param string $token
	 * @param boolean $neverClearSession
	 * @return boolean
	 * @api
	 */
	public function isRequestAllowed($request, $token = NULL, $neverClearSession = FALSE);

	/**
	 * Checks if the Ajax request is allowed after a CSRF-protect-validity-check.
	 *
	 * @param object $request
	 * @return boolean
	 * @api
	 */
	public function isAjaxRequestAllowed($request);

	/**
	 * Generate and return a new token for the $uri.
	 *
	 * @param object $request
	 * @param string $uri
	 * @param boolean $sessionHash
	 * @return string
	 * @api
	 */
	public function generateToken($request, $uri = '', $sessionHash = FALSE);

	/**
	 * Generates and returns 1st-step tokens of js-dependent protection.
	 *
	 * @param object $request
	 * @param array $encodedUrls
	 * @return array
	 * @api
	 */
	public function generateAjaxTokens($request, array $encodedUrls);

	/**
	 * Creates and stores a 2nd-step jsToken for js-dependent protection.
	 *
	 * @param object $request
	 * @param string $uri
	 * @return void
	 * @api
	 */
	public function createAndStoreJsToken($request, $uri);

	# @LOW so is this api or not? and if so, what object is $tag going to be?
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
	 * Gets encodedUrl from header.
	 *
	 * @return string
	 * @api
	 */
	public function getEncodedUrlFromHeader();

	/**
	 * Gets header keyname for token.
	 *
	 * @return string
	 * @api
	 */
	public function getTokenHeaderKey();

	/**
	 * Get token key-name.
	 *
	 * @return string
	 * @api
	 */
	public function getTokenKey();

	/**
	 * Retrieves uri to base token on.
	 *
	 * @return string
	 * @api
	 */
	public function getTokenUri();

	/**
	 * Sets token uri.
	 *
	 * @param string $tokenUri
	 * @return void
	 * @api
	 */
	public function setTokenUri($tokenUri);

	/**
	 * Retrieves request uri.
	 *
	 * @return string
	 * @api
	 */
	public function getRequestUri();

	/**
	 * Retrieves base uri.
	 *
	 * @return string
	 * @api
	 */
	public function getBaseUri();

	/**
	 * Returns whether the service is enabled.
	 *
	 * @return boolean
	 * @api
	 */
	public function isEnabled();

	/**
	 * Returns whether the service is set to depend
	 * on JavaScript mechanisms.
	 *
	 * @return boolean
	 * @api
	 */
	public function hasJsDependency();

	/**
	 * Returns whether the service is set to depend
	 * on the existence of a referrer header.
	 *
	 * @return boolean
	 * @api
	 */
	public function hasReferrerDependency();

	/**
	 * Returns whether the service is set to use a new token
	 * per request, as opposed to per session.
	 *
	 * @return boolean
	 * @api
	 */
	public function hasNewTokenPerRequest();

}
?>