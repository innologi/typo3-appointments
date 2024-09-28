<?php

defined('TYPO3') or die();

// add the flexform
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'appointments_agenda',
    'FILE:EXT:appointments/Configuration/FlexForms/flexform_agenda.xml',
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'appointments_list',
    'FILE:EXT:appointments/Configuration/FlexForms/flexform_list.xml',
);
# @CGL do we still need this?
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['appointments_agenda'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['appointments_list'] = 'pi_flexform';

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
