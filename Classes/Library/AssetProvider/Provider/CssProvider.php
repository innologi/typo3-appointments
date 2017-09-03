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
 * CSS Asset Provider
 *
 * Utilizes TYPO3 PageRenderer, ContentObject, TSFE and PageGenerator api
 *
 * @package InnologiLibs
 * @subpackage AssetProvider
 * @author Frenck Lutke
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CssProvider extends ProviderAbstract {

	/**
	 * Default asset configuration as utilized by PageRenderer
	 *
	 * Notes:
	 * - 'rel' isn't influenced by any 'alternate' property, but you can overrule it
	 * - 'allWrap' and 'allWrap.splitChar' are currently not supported
	 *
	 * @var array
	 */
	protected $defaultConfiguration = array(
		'rel' => 'stylesheet',
		'media' => 'all',
		'title' => '',
		'disableCompression' => FALSE,
		'forceOnTop' => FALSE,
		'excludeFromConcatenation' => FALSE
	);

	/**
	 * Add Library Asset
	 *
	 * @param array $conf
	 * @param string $id
	 * @return void
	 */
	public function addLibrary(array $conf, $id = '') {
		// @see http://docs.typo3.org/typo3cms/TyposcriptReference/Setup/Page/Index.html#includecsslibs-array
		$this->pageRenderer->addCssLibrary(
			$conf['file'],
			$conf['rel'],
			$conf['media'],
			$conf['title'],
			!((bool) $conf['disableCompression']),
			(bool) $conf['forceOnTop'],
			'',
			(bool) $conf['excludeFromConcatenation']
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
		// @see http://docs.typo3.org/typo3cms/TyposcriptReference/Setup/Page/Index.html#includecss-array
		$this->pageRenderer->addCssFile(
			$conf['file'],
			$conf['rel'],
			$conf['media'],
			$conf['title'],
			!((bool) $conf['disableCompression']),
			(bool) $conf['forceOnTop'],
			'',
			(bool) $conf['excludeFromConcatenation']
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
		// @TODO test this
		// @see \TYPO3\CMS\Frontend\Page\PageGenerator::renderContentWithHeader() (~line:431 @ v8.7.4)
		if (isset($GLOBALS['TSFE']->config['config']['inlineStyle2TempFile']) && $GLOBALS['TSFE']->config['config']['inlineStyle2TempFile']) {
			$conf['file'] = PageGenerator::inline2TempFile($inline, 'css');
			$this->addFile($conf, $id);
		} else {
			// @see http://docs.typo3.org/typo3cms/TyposcriptReference/Setup/Page/Index.html#cssinline
			$this->pageRenderer->addCssInlineBlock(
				$name,
				$block,
				!((bool) $conf['disableCompression']),
				(bool) $conf['forceOnTop']
			);
		}
	}

}
