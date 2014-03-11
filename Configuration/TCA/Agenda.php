<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$appointments_agenda_checkboxes = array(
		'type' => 'check',
		'items' => array(
				array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.types.create', ''),
				array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.types.update', ''),
				array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.types.delete', '')
		),
		'default' => 7
);

$TCA['tx_appointments_domain_model_agenda'] = array(
	'ctrl' => $TCA['tx_appointments_domain_model_agenda']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, holidays, types, email_address, email_text, email_types, email_owner_types, email_field_types, calendar_invite_address, calendar_invite_text, calendar_invite_types',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, types, holidays,
				--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.div.email,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.palette.email_types;email_types,
						email_address, email_text,
				--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.div.calendar_invite,calendar_invite_types, calendar_invite_address, calendar_invite_text,
				--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime'
		), #@LOW de div, palette, types item labels verplaatsen naar locallang_be?
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
		'email_types' => array(
				'showitem' => 'email_types, email_owner_types, email_field_types',
				'canNotCollapse' => 1
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
				'foreign_table' => 'tx_appointments_domain_model_agenda',
				'foreign_table_where' => 'AND tx_appointments_domain_model_agenda.pid=###CURRENT_PID### AND tx_appointments_domain_model_agenda.sys_language_uid IN (-1,0)',
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
		'name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'holidays' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.holidays',
			'config' => array(
				'type' => 'text',
				'cols' => 10,
				'rows' => 15,
				'eval' => 'nospace' #@LOW custom regexp validation
			),
		),
		'types' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.types', #@TODO _csh
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_appointments_domain_model_type',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,
			),
		),
		'email_address' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.email_address',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tt_address',
				'MM' => 'tx_appointments_agenda_address_mm',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,
				'wizards' => array(
					'_PADDING' => 1,
					'_VERTICAL' => 1,
					'edit' => array(
						'type' => 'popup',
						'title' => 'Edit',
						'script' => 'wizard_edit.php',
						'icon' => 'edit2.gif',
						'popup_onlyOpenIfSelected' => 1,
						'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
						),
					'add' => Array(
						'type' => 'script',
						'title' => 'Create new',
						'icon' => 'add.gif',
						'params' => array(
							'table' => 'tt_address',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'prepend'
							),
						'script' => 'wizard_add.php',
					),
				),
			),
		),
		'email_text' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.email_text',
			'config' => array(
				'type' => 'text',
				'cols' => 48,
				'rows' => 15,
				'eval' => 'trim',
				'default' => 'User: ###USER###<br />Agenda: ###AGENDA###<br />Type: ###TYPE###<br />Time: ###DATE###, ###START_TIME### - ###END_TIME###<br /><br />Address:<br />###ADDRESS###<br /><br />Notes:<br />###NOTES###'
			),
			'defaultExtras' => 'richtext[]'
		),
		'calendar_invite_address' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.calendar_invite_address',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tt_address',
				'MM' => 'tx_appointments_agenda_calendarinviteaddress_address_mm',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,
				'wizards' => array(
					'_PADDING' => 1,
					'_VERTICAL' => 1,
					'edit' => array(
						'type' => 'popup',
						'title' => 'Edit',
						'script' => 'wizard_edit.php',
						'icon' => 'edit2.gif',
						'popup_onlyOpenIfSelected' => 1,
						'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
						),
					'add' => Array(
						'type' => 'script',
						'title' => 'Create new',
						'icon' => 'add.gif',
						'params' => array(
							'table' => 'tt_address',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'prepend'
							),
						'script' => 'wizard_add.php',
					),
				),
			),
		),
		'calendar_invite_text' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.calendar_invite_text',
			'config' => array(
				'type' => 'text',
				'cols' => 48,
				'rows' => 15,
				'eval' => 'trim',
				'default' => "Address:\n###ADDRESS###\n\nNotes:\n###NOTES###"
			),
		),
		'email_types' => array(
				'exclude' => 0,
				'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.email_types',
				'config' => $appointments_agenda_checkboxes
		),
		'email_owner_types' => array(
				'exclude' => 0,
				'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.email_owner_types',
				'config' => $appointments_agenda_checkboxes
		),
		'email_field_types' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.email_field_types',
			'config' => $appointments_agenda_checkboxes
		),
		'calendar_invite_types' => array(
				'exclude' => 0,
				'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.calendar_invite_types',
				'config' => $appointments_agenda_checkboxes
		),
	),
);

?>