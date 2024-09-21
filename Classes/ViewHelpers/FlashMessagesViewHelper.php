<?php
namespace Innologi\Appointments\ViewHelpers;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * Flash Messages Viewhelper
 *
 * Renders Flash Messages without caching the page with them.
 * While not an ideal solution, this is TYPO3 6.2 behaviour and
 * what I am to deliver to the main sponsor into TYPO3 8.7.
 *
 * @package appointments
 * @see https://forge.typo3.org/issues/72703
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FlashMessagesViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\FlashMessagesViewHelper {

	/**
	 *
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 */
	public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$content = parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);

		// disable cache if there are flashmessages to render
		if (isset($content[0]) && isset($GLOBALS['TSFE'])) {
			/** @var ConfigurationManager $configurationManager */
			$configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
			// @extensionScannerIgnoreLine getContentObject() false positive
			if ($configurationManager->getContentObject()->getUserObjectType() === \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::OBJECTTYPE_USER) {
				$GLOBALS['TSFE']->no_cache = 1;
			}
		}

		return $content;
	}

}