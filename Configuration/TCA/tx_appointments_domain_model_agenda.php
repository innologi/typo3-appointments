<?php
defined('TYPO3_MODE') or die();

$appointments_agenda_checkboxes = [
		'type' => 'check',
		'items' => [
				['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.types.create', ''],
				['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.types.update', ''],
				['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.types.delete', '']
		],
		'default' => 7
];

return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		],
		'iconfile' => 'EXT:appointments/Resources/Public/Icons/tx_appointments_domain_model_agenda.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, holidays, types, email_address, email_text, email_types, email_owner_types, email_field_types, calendar_invite_address, calendar_invite_text, calendar_invite_types',
	],
	'types' => [
		'0' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, types, holidays,
				--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.div.email,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.palette.email_types;email_types,
						email_address, email_text,
				--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.div.calendar_invite,calendar_invite_types, calendar_invite_address, calendar_invite_text,
				--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime, endtime'
		], #@LOW de div, palette, types item labels verplaatsen naar locallang_be?
	],
	'palettes' => [
		'email_types' => ['showitem' => 'email_types, email_owner_types, email_field_types']
	],
	'columns' => [
		'sys_language_uid' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.language',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => [
					['LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages', -1],
					['LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.default_value', 0]
				],
				'default' => 0,
				'fieldWizard' => [
					'selectIcons' => [
						'disabled' => FALSE,
					],
				],
			]
		],
		'l10n_parent' => [
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => TRUE,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['', 0]
				],
				'foreign_table' => 'sys_category',
				'foreign_table_where' => 'AND sys_category.uid=###REC_FIELD_l10n_parent### AND sys_category.sys_language_uid IN (-1,0)',
				'default' => 0
			]
		],
		'l10n_diffsource' => [
			'config' => [
				'type' => 'passthrough',
				'default' => ''
			]
		],
		't3ver_label' => [
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			]
		],
		'hidden' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
			],
		],
		'starttime' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
			'config' => [
				'type' => 'input',
				'renderType' => 'inputDateTime',
				'eval' => 'datetime',
				'default' => 0,
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE,
				]
			]
		],
		'endtime' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
			'config' => [
				'type' => 'input',
				'renderType' => 'inputDateTime',
				'eval' => 'datetime',
				'default' => 0,
				'range' => [
					'upper' => mktime(0, 0, 0, 1, 1, 2038),
				],
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE,
				]
			]
		],
		'name' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			],
		],
		'holidays' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.holidays',
			'config' => [
				'type' => 'text',
				'cols' => 10,
				'rows' => 15,
				'eval' => 'nospace' #@LOW custom regexp validation
			],
		],
		'types' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.types', #@TODO _csh
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_appointments_domain_model_type',
				'foreign_sortby' => 'sorting',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,
			],
		],
		'email_address' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.email_address',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tt_address',
				'MM' => 'tx_appointments_agenda_address_mm',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,
				'fieldControl' => [
					'editPopup' => [
						'disabled' => FALSE,
					],
					'addRecord' => [
						'disabled' => FALSE,
					],
				],
			],
		],
		'email_text' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.email_text',
			'config' => [
				'type' => 'text',
				'cols' => 48,
				'rows' => 15,
				'eval' => 'trim',
				'default' => 'User: ###USER###<br />Agenda: ###AGENDA###<br />Type: ###TYPE###<br />Time: ###DATE###, ###START_TIME### - ###END_TIME###<br /><br />Address:<br />###ADDRESS###<br /><br />Notes:<br />###NOTES###',
				'enableRichtext' => TRUE
			]
		],
		'calendar_invite_address' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.calendar_invite_address',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tt_address',
				'MM' => 'tx_appointments_agenda_calendarinviteaddress_address_mm',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,
				'fieldControl' => [
					'editPopup' => [
						'disabled' => FALSE,
					],
					'addRecord' => [
						'disabled' => FALSE,
					],
				],
			],
		],
		'calendar_invite_text' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.calendar_invite_text',
			'config' => [
				'type' => 'text',
				'cols' => 48,
				'rows' => 15,
				'eval' => 'trim',
				'default' => "Address:\n###ADDRESS###\n\nNotes:\n###NOTES###"
			],
		],
		'email_types' => [
				'exclude' => 0,
				'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.email_types',
				'config' => $appointments_agenda_checkboxes
		],
		'email_owner_types' => [
				'exclude' => 0,
				'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.email_owner_types',
				'config' => $appointments_agenda_checkboxes
		],
		'email_field_types' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.email_field_types',
			'config' => $appointments_agenda_checkboxes
		],
		'calendar_invite_types' => [
				'exclude' => 0,
				'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_agenda.calendar_invite_types',
				'config' => $appointments_agenda_checkboxes
		],
	],
];