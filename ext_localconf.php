<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Agenda',
	array(
		'Agenda' => 'showMonth, showWeeks',

	),
	// non-cacheable actions
	array(

	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'List',
	array(
		'Appointment' => 'list, show, new, create, edit, update, delete, free',

	),
	// non-cacheable actions
	array(
		'Appointment' => 'create, update, delete, edit, new, free',
			#@TODO onzeker: edit
	)
);

## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder

#$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'Tx_Appointments_Configuration_TCA_PostProcess_Appointment';
#$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'Tx_Appointments_Configuration_TCA_PostProcess_FormFieldValue';
?>