<?php

defined('TYPO3') or die();

// add static ts
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'appointments',
    'Configuration/TypoScript',
    'Appointment Scheduler',
);
