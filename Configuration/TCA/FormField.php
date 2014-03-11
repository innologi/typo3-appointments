<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_appointments_domain_model_formfield'] = array(
	'ctrl' => $TCA['tx_appointments_domain_model_formfield']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, label, csh, field_type, validation_types, choices, function, enable_field, enable_value',
	),
	'types' => array(
		'1' => array(
			'showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, title, label, csh, field_type, choices, function, validation_types,
				--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.palette.enable_field;enable_field,
				--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime'
			),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
		'enable_field' => array(
			'showitem' => 'enable_field, enable_value',
			'canNotCollapse' => 1
		),
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
				'foreign_table' => 'tx_appointments_domain_model_formfield',
				'foreign_table_where' => 'AND tx_appointments_domain_model_formfield.pid=###CURRENT_PID### AND tx_appointments_domain_model_formfield.sys_language_uid IN (-1,0)',
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
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.title',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'label' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.label',
			'config' => array(
				'type' => 'text',
				'cols' => 48,
				'rows' => 2,
				'eval' => 'trim,required'
			),
		),
		'csh' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.csh',
			'config' => array(
				'type' => 'text',
				'cols' => 48,
				'rows' => 5,
				'eval' => 'trim'
			),
		),
		'validation_types' => array(
				'exclude' => 0,
				'displayCond' => 'FIELD:field_type:!=:'.Tx_Appointments_Domain_Model_FormField::TYPE_BOOLEAN,
				'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types',
				'config' => array(
						'type' => 'select',
						'items' => array(
								#array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.alphanum',Tx_Appointments_Domain_Model_FormField::VALIDATE_ALPHANUMERIC),
								array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.datetime',Tx_Appointments_Domain_Model_FormField::VALIDATE_DATE_TIME),
								array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.email',Tx_Appointments_Domain_Model_FormField::VALIDATE_EMAIL_ADDRESS),
								array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.float',Tx_Appointments_Domain_Model_FormField::VALIDATE_FLOAT),
								array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.integer',Tx_Appointments_Domain_Model_FormField::VALIDATE_INTEGER),
								array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.notempty',Tx_Appointments_Domain_Model_FormField::VALIDATE_NOT_EMPTY),
								array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.number',Tx_Appointments_Domain_Model_FormField::VALIDATE_NUMBER),
								#array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.string',Tx_Appointments_Domain_Model_FormField::VALIDATE_STRING),
								#array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.text',Tx_Appointments_Domain_Model_FormField::VALIDATE_TEXT),
								array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.naturalnumber',Tx_Appointments_Domain_Model_FormField::VALIDATE_NATURALNUMBER),
						),
						'size' => 7,
						'maxitems' => 99,
						'multiple' => 0
				),
		),
		'field_type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type.boolean',Tx_Appointments_Domain_Model_FormField::TYPE_BOOLEAN),
					array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type.radio',Tx_Appointments_Domain_Model_FormField::TYPE_RADIO),
					array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type.select',Tx_Appointments_Domain_Model_FormField::TYPE_SELECT),
					array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type.textsmall',Tx_Appointments_Domain_Model_FormField::TYPE_TEXTSMALL),
					array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type.textlarge',Tx_Appointments_Domain_Model_FormField::TYPE_TEXTLARGE)
				),
				'size' => 1,
				'maxitems' => 1,
				'default' => Tx_Appointments_Domain_Model_FormField::TYPE_TEXTSMALL
			),
		),
		'choices' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:field_type:IN:'.Tx_Appointments_Domain_Model_FormField::TYPE_SELECT . ',' . Tx_Appointments_Domain_Model_FormField::TYPE_RADIO,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.choices',
			'config' => array(
				'type' => 'text',
				'cols' => 48,
				'rows' => 5,
				'eval' => 'trim'
			),
		),
		'function' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:field_type:IN:'.Tx_Appointments_Domain_Model_FormField::TYPE_TEXTLARGE.','.Tx_Appointments_Domain_Model_FormField::TYPE_TEXTSMALL,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.function',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.function.info',Tx_Appointments_Domain_Model_FormField::FUNCTION_INFORMATIONAL),
					array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.function.addtime',Tx_Appointments_Domain_Model_FormField::FUNCTION_ADDTIME),
					array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.function.email',Tx_Appointments_Domain_Model_FormField::FUNCTION_EMAIL)
				),
				'size' => 1,
				'maxitems' => 1
			),
		),
		'enable_field' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.enable_field',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.enable_field.default', 0),
					array('LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.div.enable_field', '--div--'),
				),
				'foreign_table' => 'tx_appointments_domain_model_formfield',
				/*
				 * only allow formfields other than this, that are of the same type,
				 * and have select as field_type. But also don't allow any chaining
				 * as it is currently not supported: don't allow fields enabled by
				 * other fields, or ANY field if current one is set as enable_field
				 * for another field.
				 */
				// @LOW Add support for all field types, eventually
				'foreign_table_where' => '
					AND tx_appointments_domain_model_formfield.uid <> ###THIS_UID###
					AND tx_appointments_domain_model_formfield.type = ###REC_FIELD_type###
					AND tx_appointments_domain_model_formfield.field_type IN(' . Tx_Appointments_Domain_Model_FormField::TYPE_SELECT . ',' . Tx_Appointments_Domain_Model_FormField::TYPE_RADIO . ')
					AND tx_appointments_domain_model_formfield.enable_field = 0
					AND 0=(
						SELECT COUNT(*)
						FROM tx_appointments_domain_model_formfield
						WHERE enable_field = ###THIS_UID###
					)
					ORDER BY tx_appointments_domain_model_formfield.sorting ASC',
				// 'MM' => 'tx_appointments_formfield_mm', // @LOW allow multiple choices?
				'size' => 1,
				// 'autoSizeMax' => 30,
				'minitems' => 0,
				'maxitems' => 1,
				'multiple' => 0,
			),
		),
		'enable_value' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.enable_value',
			'config' => array(
				'type' => 'input',
				'size' => 50,
				'eval' => 'trim'
			),
		),
		'type' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
	),
);

?>