<?php
namespace Innologi\Appointments\Core;
/***************************************************************
 *  Copyright notice
*
*  (c) 2013 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\Core\Bootstrap;
/**
 * Extbase Bootstrap for tasks (pre-CommandControllers)
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class BootstrapTask extends Bootstrap {

	/**
	 * Extension name
	 *
	 * @var string
	 */
	protected $extensionName;

	/**
	 * Plugin name
	 *
	 * @var string
	 */
	protected $pluginName;

	/**
	 * __construct
	 *
	 * @param string $extensionName
	 * @param string $pluginName
	 * @param integer $parameters
	 * @return void
	 */
	public function __construct($extensionName, $pluginName, $parameters) {
		$this->extensionName = $extensionName;
		$this->pluginName = $pluginName;
		$this->setupFramework($parameters);
	}

	/**
	 * __destruct
	 *
	 * @return void
	 */
	public function __destruct() {
		$this->tearDownFramework();
	}

	/**
	 * Initializes framework through configuration
	 *
	 * @param array $parameters
	 * @return void
	 */
	protected function setupFramework($parameters) {
		$configuration = array(
				'extensionName' => $this->extensionName,
				'pluginName' => 'tx_'.$this->extensionName.'_'.$this->pluginName,
				'settings' => '< module.tx_'.$this->extensionName.'.settings',
				'persistence' => '< module.tx_'.$this->extensionName.'.persistence',
				'_LOCAL_LANG' => '< module.tx_'.$this->extensionName.'._LOCAL_LANG' //e.g. module.tx_extname._LOCAL_LANG.default.llangkey
		);
		foreach ($parameters as $key=>$array) {
			$configuration[$key.'.'] = $array;
		}
		$this->initialize($configuration);
	}

	/**
	 * Tears down framework in appropriate order
	 *
	 * @return void
	 */
	protected function tearDownFramework() {
		$this->persistenceManager->persistAll();
		$this->reflectionService->shutdown();
	}

}

?>