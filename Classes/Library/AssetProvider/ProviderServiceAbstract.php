<?php
namespace Innologi\Appointments\Library\AssetProvider;
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
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\SingletonInterface;
/**
 * TYPO3 Extbase Asset Provider Service Abstract
 *
 * @package InnologiLibs
 * @subpackage AssetProvider
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class ProviderServiceAbstract implements ProviderServiceInterface, SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Asset-loading configuration
	 *
	 * @var array
	 */
	protected $configuration = array(
		'default' => array()
	);

	/**
	 * Asset-loading typoscript
	 *
	 * @var array
	 */
	protected $typoscript = array(
		'default.' => array()
	);

	/**
	 * @var string
	 */
	protected $extensionTsKey;

	/**
	 * Class constructor
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function __construct(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		// because we want objectManager in __construct, we can't rely on DI as it is always later
		$this->objectManager = $objectManager;
		$this->initializeConfiguration();
	}

	/**
	 * Initializes the configuration
	 *
	 * @return void
	 */
	protected function initializeConfiguration() {
		/* @var $configurationManager \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface */
		$configurationManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
		$frameworkConfiguration = $configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
		if (isset($frameworkConfiguration['assets'])) {
			$this->configuration = array_merge(
				$this->configuration,
				$frameworkConfiguration['assets']
			);
		}

		// inline configurations require the original TS
		$originalTypoScript = $configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
		);
		if (isset($originalTypoScript['plugin.'][$this->extensionTsKey]['assets.'])) {
			$this->typoscript = array_merge(
				$this->typoscript,
				$originalTypoScript['plugin.'][$this->extensionTsKey]['assets.']
			);
		}
	}

}
