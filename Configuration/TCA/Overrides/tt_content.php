<?php

defined('TYPO3') or die();

// register plugins
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Appointments',
    'Agenda',
    'LLL:EXT:appointments/Resources/Private/Language/locallang_be.xml:tx_appointments_plugin_agenda_title',
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Appointments',
    'List',
    'LLL:EXT:appointments/Resources/Private/Language/locallang_be.xml:tx_appointments_plugin_list_title',
);

// add the flexform
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:appointments/Configuration/FlexForms/flexform_agenda.xml',
    'appointments_agenda',
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:appointments/Configuration/FlexForms/flexform_list.xml',
    'appointments_list',
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', 'appointments_agenda', 'after:subheader');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', 'appointments_list', 'after:subheader');
