<?php
namespace Innologi\Appointments\Mvc\ContentObject;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Resource\FilePathSanitizer;

/**
 * Contains FILE class object.
 *
 * Smaller version of the TYPO3 v9.5 FILE content object, for use with TEMPLATE until I completely migrate to FLUIDTEMPLATE.
  */
class FileContentObject extends AbstractContentObject
{
    /**
     * Rendering the cObject, FILE
     *
     * @param array $conf Array of TypoScript properties
     * @return string Output
     */
    public function render($conf = [])
    {
        $theValue = '';
        $file = isset($conf['file.']) ? $this->cObj->stdWrap($conf['file'], $conf['file.']) : $conf['file'];
        try {
            $file = GeneralUtility::makeInstance(FilePathSanitizer::class)->sanitize($file);
            if (file_exists($file) && filesize($file) < 1024 * 1024) {
                $theValue = file_get_contents($file);
            }
        } catch (\TYPO3\CMS\Core\Resource\Exception $e) {
            // do nothing
        }
        $linkWrap = isset($conf['linkWrap.']) ? $this->cObj->stdWrap($conf['linkWrap'], $conf['linkWrap.']) : $conf['linkWrap'];
        if ($linkWrap) {
            $theValue = $this->cObj->linkWrap($theValue, $linkWrap);
        }
        $wrap = isset($conf['wrap.']) ? $this->cObj->stdWrap($conf['wrap'], $conf['wrap.']) : $conf['wrap'];
        if ($wrap) {
            $theValue = $this->cObj->wrap($theValue, $wrap);
        }
        if (isset($conf['stdWrap.'])) {
            $theValue = $this->cObj->stdWrap($theValue, $conf['stdWrap.']);
        }
        return $theValue;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
