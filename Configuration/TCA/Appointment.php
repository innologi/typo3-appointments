<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_appointments_domain_model_appointment'] = array(
	'ctrl' => $TCA['tx_appointments_domain_model_appointment']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, creation_progress, refresh, begin_time, end_time, begin_reserved, end_reserved, notes, notes_su, type, form_field_values, address, fe_user, agenda',
	),
	'types' => array( #@TODO kunnen we creation_progress field laten bepalen wat we zien?
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, agenda, type,
				--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.palette.times;times,
				notes, notes_su, fe_user,
				--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.form_field_values,form_field_values,
				--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.address,address,
				--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime'
		),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
		'times' => array(
			'showitem' => 'begin_time, end_time,
					--linebreak--,
					begin_reserved, end_reserved',
			'canNotCollapse' => 1,
		)
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_appointments_domain_model_appointment',
				'foreign_table_where' => 'AND tx_appointments_domain_model_appointment.pid=###CURRENT_PID### AND tx_appointments_domain_model_appointment.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => 1
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'creation_progress' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.creation_progress',
			'config' => array(
				'type' => 'none',
			),
		),
		'refresh' => array(
			'exclude' => 1,
			'label' => 'Refresh', #@TODO llang
			'config' => array(
				'type' => 'none',
			),
		),
		'begin_time' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.begin_time',
			'config' => array(
				'type' => 'input',
				'size' => 12,
				'eval' => 'datetime,required',
				'checkbox' => 1,
				'default' => time()
			),
		),
		'end_time' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.end_time',
			'config' => array(
				'type' => 'input',
				'size' => 12,
				'eval' => 'datetime,required',
				'checkbox' => 1,
				#'default' => time(),
				#'readOnly' => 1
			),
		),
		'begin_reserved' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.begin_reserved',
			'config' => array(
				'type' => 'input',
				'size' => 12,
				'eval' => 'datetime,required',
				'checkbox' => 1,
				#'default' => time(),
				#'readOnly' => 1
			),
		),
		'end_reserved' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.end_reserved',
			'config' => array(
				'type' => 'input',
				'size' => 12,
				'eval' => 'datetime,required',
				'checkbox' => 1,
				#'default' => time(),
				#'readOnly' => 1
			),
		),
		'notes' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.notes',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim'
			),
		),
		'notes_su' => array(
				'exclude' => 0,
				'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.notes_su',
				'config' => array(
						'type' => 'text',
						'cols' => 40,
						'rows' => 15,
						'eval' => 'trim'
				),
		),
		'type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.type',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_appointments_domain_model_type',
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'form_field_values' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.form_field_values',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_appointments_domain_model_formfieldvalue',
				'foreign_field' => 'appointment',
				'foreign_unique' => 'form_field',
					//NOTE: extbase supports propertypaths here for showing but NOT updating (FE only, 4.7.8), create an extbase patch?
				'foreign_sortby' => 'sorting',
				'maxitems' => 9999,
				'appearance' => array(
					'collapseAll' => 1,
					'levelLinksPosition' => 'top',
					'showSynchronizationLink' => 1,
					'showPossibleLocalizationRecords' => 1,
					'showAllLocalizationLink' => 1 #@TODO __how to enable drag 'n drop sorting again?
				),
			),
		),
		'address' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.address',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tt_address', #@TODO foreign_types? [4.7]
				'minitems' => 1,
				'maxitems' => 1,
				'appearance' => array(
					'collapseAll' => 0,
					'levelLinksPosition' => 'top',
					'showSynchronizationLink' => 1,
					'showPossibleLocalizationRecords' => 1,
					'showAllLocalizationLink' => 1
				),
			),
		),
		'fe_user' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.fe_user',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'fe_users',
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'agenda' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.agenda',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_appointments_domain_model_agenda',
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
	),
);

?>