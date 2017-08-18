<?php
defined('TYPO3_MODE') or die();

return [
	'ctrl' => array(
		'title'	=> 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfieldvalue',
		'label' => 'form_field',
		'label_alt' => 'value',
		'label_alt_force' => TRUE,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'hideTable' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('appointments') . 'Resources/Public/Icons/tx_appointments_domain_model_formfieldvalue.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, value, form_field',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, form_field, value, --div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
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
				'foreign_table' => 'tx_appointments_domain_model_formfieldvalue',
				'foreign_table_where' => 'AND tx_appointments_domain_model_formfieldvalue.pid=###CURRENT_PID### AND tx_appointments_domain_model_formfieldvalue.sys_language_uid IN (-1,0)',
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
		'value' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfieldvalue.value',
			'config' => array(
				'type' => 'text', #@LOW make this somehow depend on formfield field type? eval as well
				'cols' => 48,
				'rows' => 3,
				'eval' => 'trim'
			),
		),
		'form_field' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfieldvalue.form_field',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_appointments_domain_model_formfield',
				'foreign_table_where' => '
					AND tx_appointments_domain_model_formfield.hidden=0
					AND tx_appointments_domain_model_formfield.type=COALESCE((
						SELECT a.type
						FROM tx_appointments_domain_model_appointment a,tx_appointments_domain_model_formfieldvalue ffv
						WHERE a.uid=ffv.appointment AND ffv.uid=###THIS_UID###
					),(
						SELECT type
						FROM tx_appointments_domain_model_appointment
						WHERE uid=###THIS_UID###
					))
					GROUP BY tx_appointments_domain_model_formfield.uid',
					//note COALESCE(): because appointment.formfieldvalues has 'foreign_unique' set, it already
					//retrieves this fields' values from the appointment context, where THIS_UID is the appointment UID
					//which will very often result in NULL from the first subquery, which then triggers the second
					//subquery so we at least get relevant results, otherwise we wouldn't be allowed to add any from TCA!
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'appointment' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
	),
];