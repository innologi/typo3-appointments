<?php
defined('TYPO3_MODE') or die();

return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment',
		'label' => 'begin_time',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'default_sortby' => 'ORDER BY begin_time DESC',
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
			'tx_appointments_creation_progress' => 'creation_progress', #@TODO add to enable fields with hook $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['addEnableColumns']
		],
		'iconfile' => 'EXT:appointments/Resources/Public/Icons/tx_appointments_domain_model_appointment.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, creation_progress, begin_time, end_time, begin_reserved, end_reserved, notes, notes_su, type, form_field_values, address, fe_user, agenda',
	],
	'types' => [
		'1' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, agenda, type,
				--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.palette.times;times,
				notes, notes_su, fe_user,
				--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.form_field_values,form_field_values,
				--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.address,address,
				--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime'
		],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
		'times' => [
			'showitem' => 'begin_time, end_time,
					--linebreak--,
					begin_reserved, end_reserved',
			'canNotCollapse' => 1,
		]
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
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			]
		],
		'hidden' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => [
				'type' => 'check',
				'default' => 1
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
		'creation_progress' => [ #@TODO gebruik de displayCond en een user function misschien, om tekstueel toe te lichten wat de status is?
			'exclude' => 1,
			#'displayCond' => 'FIELD:creation_progress:>:0',
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.creation_progress',
			'config' => [
				'type' => 'none',
			],
		],
		'begin_time' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.begin_time',
			'config' => [
				'type' => 'input',
				'renderType' => 'inputDateTime',
				'size' => 12,
				'eval' => 'datetime,required',
				'checkbox' => 1,
				'default' => time()
			],
		],
		'end_time' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.end_time',
			'config' => [
				'type' => 'input',
				'renderType' => 'inputDateTime',
				'size' => 12,
				'eval' => 'datetime,required',
				'checkbox' => 1,
				#'default' => time(),
				#'readOnly' => 1
			],
		],
		'begin_reserved' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.begin_reserved',
			'config' => [
				'type' => 'input',
				'renderType' => 'inputDateTime',
				'size' => 12,
				'eval' => 'datetime,required',
				'checkbox' => 1,
				#'default' => time(),
				#'readOnly' => 1
			],
		],
		'end_reserved' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.end_reserved',
			'config' => [
				'type' => 'input',
				'renderType' => 'inputDateTime',
				'size' => 12,
				'eval' => 'datetime,required',
				'checkbox' => 1,
				#'default' => time(),
				#'readOnly' => 1
			],
		],
		'notes' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.notes',
			'config' => [
				'type' => 'text',
				'cols' => 48,
				'rows' => 6,
				'eval' => 'trim'
			],
		],
		'notes_su' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.notes_su',
			'config' => [
				'type' => 'text',
				'cols' => 48,
				'rows' => 6,
				'eval' => 'trim'
			],
		],
		'type' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.type',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'tx_appointments_domain_model_type',
				'minitems' => 0,
				'maxitems' => 1,
			],
		],
		'form_field_values' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.form_field_values',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_appointments_domain_model_formfieldvalue',
				'foreign_field' => 'appointment',
				'foreign_unique' => 'form_field', #@LOW there seems to be a bug that will show the first item, regardless if it is in use
					//NOTE: extbase supports propertypaths here for showing but NOT updating (FE only, 4.7.8), create an extbase patch?
				'foreign_sortby' => 'sorting',
				'maxitems' => 9999,
				'appearance' => [
					'collapseAll' => 1,
					'levelLinksPosition' => 'top',
					'useSortable' => 1,
					'showSynchronizationLink' => 1,
					'showPossibleLocalizationRecords' => 1,
					'showAllLocalizationLink' => 1
				],
			],
		],
		'address' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.address',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tt_address', #@TODO foreign_types? [4.7] or proper implementation of an extbase type?
				'minitems' => 0,
				'maxitems' => 1,
				'appearance' => [
					'collapseAll' => 0,
					'levelLinksPosition' => 'top',
					'showSynchronizationLink' => 1,
					'showPossibleLocalizationRecords' => 1,
					'showAllLocalizationLink' => 1
				],
			],
		],
		'fe_user' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.fe_user',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'fe_users',
				'minitems' => 0,
				'maxitems' => 1,
			],
		],
		'agenda' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_appointment.agenda',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'tx_appointments_domain_model_agenda',
				'minitems' => 0,
				'maxitems' => 1,
			],
		],
	],
];