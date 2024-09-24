<?php

defined('TYPO3_MODE') or die();

#@LOW make it set a type as soon as IRRE supports it
#if (!isset($GLOBALS['TCA']['tt_address']['ctrl']['type'])) {
#	$GLOBALS['TCA']['tt_address']['ctrl']['type'] = 'tx_extbase_type';
#}
#$GLOBALS['TCA']['tt_address']['columns'][$TCA['tt_address']['ctrl']['type']]['config']['items'][] = array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tt_address.tx_extbase_type.Tx_Appointments_Address','Tx_Appointments_Address');
#$GLOBALS['TCA']['tt_address']['types']['Tx_Appointments_Address']['showitem'] = $TCA['tt_address']['types']['1']['showitem'];
#$GLOBALS['TCA']['tt_address']['types']['Tx_Appointments_Address']['showitem'] .= ',--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address,';
#$GLOBALS['TCA']['tt_address']['types']['Tx_Appointments_Address']['showitem'] = 'first_name, middle_name, last_name, name, gender, birthday, email, address, zip, city';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tt_address',
    [
        'tx_appointments_social_security_number' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.social_security_number',
            'config' => [
                'type' => 'input',
                'size' => 25,
                'max' => 255,
                'eval' => 'trim',
            ],
        ],
        'tx_appointments_creation_progress' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.creation_progress',
            'config' => [
                'type' => 'none',
            ],
        ],
    ],
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_address',
    'tx_appointments_social_security_number',
);
