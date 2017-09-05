<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Innologi.' . $_EXTKEY,
	'Agenda',
	array(
		'Agenda' => 'showMonth, showWeeks, none',

	),
	// non-cacheable actions
	array(

	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Innologi.' . $_EXTKEY,
	'List',
	array(
		'Appointment' => 'list, show, new1, new2, processNew, simpleProcessNew, create, edit, update, delete, free, none',

	),
	// non-cacheable actions
	array(
		'Appointment' => 'list, new1, new2, processNew, simpleProcessNew, create, edit, update, delete, free',
	)
);

// create a cache specifically the date/time slots
if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['appointments_slots'])
	|| !is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['appointments_slots'])
) {
	$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['appointments_slots'] = [
		'options' => array(
			'defaultLifetime' => 3600,
			'compression' => extension_loaded('zlib')
		),
		'groups' => array('pages', 'all')
	];
}

if (TYPO3_MODE === 'BE') {
	#$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \Innologi\Appointments\Hooks\Tcemain::class;
	$TYPO3_CONF_VARS['SC_OPTIONS'][\TYPO3\CMS\Core\Imaging\IconFactory::class]['overrideIconOverlay'][] = \Innologi\Appointments\Hooks\IconFactoryHook::class;

	//add scheduler tasks
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Innologi\Appointments\Task\CleanUpTask::class] = array(
		'extension'			=> $_EXTKEY,
		'title'				=> 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xml:tx_appointments_task_cleanup.name',
		'description'		=> 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xml:tx_appointments_task_cleanup.description',
		'additionalFields'	=> \Innologi\Appointments\Task\CleanUpTaskAdditionalFieldProvider::class
	);
}