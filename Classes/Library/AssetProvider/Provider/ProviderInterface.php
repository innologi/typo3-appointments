<?php
namespace Innologi\Appointments\Library\AssetProvider\Provider;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 * Asset Provider Interface
 *
 * @package InnologiLibs
 * @subpackage AssetProvider
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface ProviderInterface {

	/**
	 * Processes configuration of asset type
	 *
	 * @param array $configuration
	 * @param array $typoscript
	 * @return void
	 */
	public function processConfiguration(array $configuration, array $typoscript);

	/**
	 * Add Library Asset
	 *
	 * @param array $conf
	 * @param string $id
	 * @return void
	 */
	public function addLibrary(array $conf, $id = '');

	/**
	 * Add File Asset
	 *
	 * @param array $conf
	 * @param string $id
	 * @return void
	 */
	public function addFile(array $conf, $id = '');

	/**
	 * Add Inline Asset
	 *
	 * @param string $inline
	 * @param array $conf
	 * @param string $id
	 * @return void
	 */
	public function addInline($inline, array $conf, $id = '');

}
