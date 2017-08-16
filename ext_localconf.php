<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	$_EXTKEY,
	'Agenda',
	array(
		'Agenda' => 'showMonth, showWeeks, none',

	),
	// non-cacheable actions
	array(

	)
);

// STRONG csrf protection levels prevent caching of some views
$noCache = '';
if (isset($TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY])) {
	$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY]);
	if (isset($extConf['csrf_protection_level'])) {
		$noCache = in_array(
			(int)$extConf['csrf_protection_level'],
			array(
				// using ext-constants in this file produces problems when the extension
				// is uninstalled but the cache isn't cleared yet
				3, //Tx_Appointments_Service_CsrfProtectServiceInterface::STRONG,
				4, //Tx_Appointments_Service_CsrfProtectServiceInterface::STRONG_PLUS
			)
		) ? ', list, show' : '';
	}
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	$_EXTKEY,
	'List',
	array(
		'Appointment' => 'list, show, new1, new2, processNew, simpleProcessNew, create, edit, update, delete, free, none, ajaxVerifyToken, ajaxGenerateTokens',

	),
	// non-cacheable actions
	array(
		'Appointment' => 'create, update, delete, edit, new1, new2, processNew, simpleProcessNew, free, ajaxVerifyToken, ajaxGenerateTokens' . $noCache,
	)
);

if (TYPO3_MODE === 'BE') {
	#$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'Tx_Appointments_Configuration_TCA_PostProcess_Appointment';
	#$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'Tx_Appointments_Configuration_TCA_PostProcess_FormFieldValue';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_iconworks.php']['overrideIconOverlay'][] = 'tx_appointments_hooks_iconworks';

	//add scheduler tasks
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tx_Appointments_Task_CleanUpTask'] = array(
			'extension'			=> $_EXTKEY,
			'title'				=> 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xml:tx_appointments_task_cleanup.name',
			'description'		=> 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xml:tx_appointments_task_cleanup.description',
			'additionalFields'	=> 'Tx_Appointments_Task_CleanUpTaskAdditionalFieldProvider'
	);
}
?>