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
use TYPO3\CMS\Frontend\Page\PageGenerator;
/**
 * Javascript Asset Provider
 *
 * Utilizes TYPO3 PageRenderer, ContentObject, TSFE and PageGenerator api
 *
 * @package InnologiLibs
 * @subpackage AssetProvider
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class JavascriptProvider extends ProviderAbstract {

	/**
	 * Default asset configuration as utilized by PageRenderer
	 *
	 * Notes:
	 * - 'allWrap' and 'allWrap.splitChar' are currently not supported
	 * - 'compress' is disabled by default for Libs via PageRenderer, possibly due
	 * to a bug with external files, but we enable it by default
	 *
	 * @var array
	 */
	protected $defaultConfiguration = array(
		'placeInFooter' => FALSE,
		'type' => 'text/javascript',
		'disableCompression' => FALSE,
		'forceOnTop' => FALSE,
		'excludeFromConcatenation' => FALSE,
		'allWrap' => '',
		'splitChar' => '|',
		'async' => FALSE,
		'integrity' => ''
	);

	/**
	 * Add Library Asset
	 *
	 * @param array $conf
	 * @param string $id
	 * @return void
	 */
	public function addLibrary(array $conf, $id = '') {
		$methodName = (bool) $conf['placeInFooter'] ? 'addJsFooterLibrary' : 'addJsLibrary';
		// @see http://docs.typo3.org/typo3cms/TyposcriptReference/Setup/Page/Index.html#includejslibs-array
		$this->pageRenderer->$methodName(
			$id,
			$conf['file'],
			$conf['type'],
			!((bool) $conf['disableCompression']),
			(bool) $conf['forceOnTop'],
			$conf['allWrap'],
			(bool) $conf['excludeFromConcatenation'],
			$conf['splitChar'],
			$conf['async'],
			$conf['integrity']
		);
	}

	/**
	 * Add File Asset
	 *
	 * @param array $conf
	 * @param string $id
	 * @return void
	 */
	public function addFile(array $conf, $id = '') {
		// @TODO test this
		$methodName = (bool) $conf['placeInFooter'] ? 'addJsFooterFile' : 'addJsFile';
		// @see http://docs.typo3.org/typo3cms/TyposcriptReference/Setup/Page/Index.html#includejs-array
		$this->pageRenderer->$methodName(
			$conf['file'],
			$conf['type'],
			!((bool) $conf['disableCompression']),
			(bool) $conf['forceOnTop'],
			$conf['allWrap'],
			(bool) $conf['excludeFromConcatenation'],
			$conf['splitChar'],
			$conf['async'],
			$conf['integrity']
		);
	}

	/**
	 * Add Inline Asset
	 *
	 * @param string $inline
	 * @param array $conf
	 * @param string $id
	 * @return void
	 */
	public function addInline($inline, array $conf, $id = '') {
		// PageRenderer does not check if removeDefaultJS is set, so we need to instead
		// @see \TYPO3\CMS\Frontend\Page\PageGenerator::renderContentWithHeader() (~line:865)
		if (isset($GLOBALS['TSFE']->config['config']['removeDefaultJS']) && $GLOBALS['TSFE']->config['config']['removeDefaultJS'] === 'external') {
			$conf['file'] = PageGenerator::inline2TempFile($inline, 'js');
			$this->addFile($conf, $id);
		} else {
			$methodName = (bool) $conf['placeInFooter'] ? 'addJsFooterInlineCode' : 'addJsInlineCode';
			// @see http://docs.typo3.org/typo3cms/TyposcriptReference/Setup/Page/Index.html#jsinline
			$this->pageRenderer->$methodName(
				$id,
				$inline,
				!((bool) $conf['disableCompression']),
				(bool) $conf['forceOnTop']
			);
		}
	}

}