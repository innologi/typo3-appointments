<?php
defined('TYPO3_MODE') or die();

$appointments_type_configStartTime = array( #@LOW add custom regex eval e.g. /([0-1]{1}[0-9]{1}|2[0-3]{1}):[0-5]{1}[0-9{1}/, see manual TCA->input->eval on how
	'type' => 'input',
	'size' => 4,
	'max' => 5,
	'default' => '08:00'
);
$appointments_type_configStopTime = $appointments_type_configStartTime;
$appointments_type_configStopTime['default'] = '20:00';
$appointments_type_configMaxAmount = array (
	'type' => 'input',
	'size' => 4,
	'max' => 4,
	'range' => array(
			'lower' => 0,
			'upper' => 1440
	),
	'eval' => 'int'
);
$appointments_type_configDefDur = $appointments_type_configMaxAmount;
$appointments_type_configMinInt = $appointments_type_configDefDur;
$appointments_type_configMinInt['range']['lower'] = 1;
$appointments_type_configMinInt['default'] = 15;
$appointments_type_configMinInt['eval'] .= ',required';

return [
	'ctrl' => array(
		'title'	=> 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
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
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('appointments') . 'Resources/Public/Icons/tx_appointments_domain_model_type.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, superuser_only, exclusive_availability, dont_block_types, dont_restrict_type_counts, default_duration, start_time_monday, start_time_tuesday, start_time_wednesday, start_time_thursday, start_time_friday, start_time_saturday, start_time_sunday, stop_time_monday, stop_time_tuesday, stop_time_wednesday, stop_time_thursday, stop_time_friday, stop_time_saturday, stop_time_sunday, exclude_holidays, max_amount_monday, max_amount_tuesday, max_amount_wednesday, max_amount_thursday, max_amount_friday, max_amount_saturday, max_amount_sunday, minute_interval_monday, minute_interval_tuesday, minute_interval_wednesday, minute_interval_thursday, minute_interval_friday, minute_interval_saturday, minute_interval_sunday, max_amount_per_var_days, per_var_days, per_var_days_interval, between_minutes, hours_mutable, blocked_hours, blocked_hours_workdays, max_days_forward, form_fields, address_disable, address_enable_name, address_enable_gender, address_enable_birthday, address_enable_address, address_enable_security, address_enable_email',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, superuser_only, exclusive_availability, dont_block_types, dont_restrict_type_counts, default_duration, between_minutes, max_days_forward, hours_mutable, blocked_hours;;2,
				--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.div.day_config,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang.xml:tx_appointments_agenda.day_1;monday,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang.xml:tx_appointments_agenda.day_2;tuesday,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang.xml:tx_appointments_agenda.day_3;wednesday,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang.xml:tx_appointments_agenda.day_4;thursday,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang.xml:tx_appointments_agenda.day_5;friday,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang.xml:tx_appointments_agenda.day_6;saturday,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang.xml:tx_appointments_agenda.day_7;sunday,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.palette.overrule;overrule,
				--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.div.address_fields,address_disable,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.name;address_name,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.social_security_number;address_security,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.gender;address_gender,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.birthday;address_birthday,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.address;address_address,
					--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.email;address_email,
				--div--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.form_fields,form_fields,
				--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime'
		),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
		'2' => array('showitem' => 'blocked_hours_workdays'),
		'monday' => array(
			'showitem' => 'max_amount_monday, start_time_monday, stop_time_monday, minute_interval_monday',
			'canNotCollapse' => 1,
		),
		'tuesday' => array(
			'showitem' => 'max_amount_tuesday, start_time_tuesday, stop_time_tuesday, minute_interval_tuesday',
			'canNotCollapse' => 1,
		),
		'wednesday' => array(
			'showitem' => 'max_amount_wednesday, start_time_wednesday, stop_time_wednesday, minute_interval_wednesday',
			'canNotCollapse' => 1,
		),
		'thursday' => array(
			'showitem' => 'max_amount_thursday, start_time_thursday, stop_time_thursday, minute_interval_thursday',
			'canNotCollapse' => 1,
		),
		'friday' => array(
			'showitem' => 'max_amount_friday, start_time_friday, stop_time_friday, minute_interval_friday',
			'canNotCollapse' => 1,
		),
		'saturday' => array(
			'showitem' => 'max_amount_saturday, start_time_saturday, stop_time_saturday, minute_interval_saturday',
			'canNotCollapse' => 1,
		),
		'sunday' => array(
			'showitem' => 'max_amount_sunday, start_time_sunday, stop_time_sunday, minute_interval_sunday',
			'canNotCollapse' => 1,
		),
		'overrule' => array(
			'showitem' => 'max_amount_per_var_days, per_var_days, per_var_days_interval, --linebreak--, exclude_holidays',
			'canNotCollapse' => 1,
		),
		'address_name' => array( #@TODO __CSH
			'showitem' => 'address_enable_name;LLL:EXT:lang/locallang_common.xml:enable',
			'canNotCollapse' => 1,
		),
		'address_gender' => array(
			'showitem' => 'address_enable_gender;LLL:EXT:lang/locallang_common.xml:enable',
			'canNotCollapse' => 1,
		),
		'address_birthday' => array(
			'showitem' => 'address_enable_birthday;LLL:EXT:lang/locallang_common.xml:enable',
			'canNotCollapse' => 1,
		),
		'address_address' => array(
			'showitem' => 'address_enable_address;LLL:EXT:lang/locallang_common.xml:enable',
			'canNotCollapse' => 1,
		),
		'address_security' => array(
			'showitem' => 'address_enable_security;LLL:EXT:lang/locallang_common.xml:enable',
			'canNotCollapse' => 1,
		),
		'address_email' => array(
			'showitem' => 'address_enable_email;LLL:EXT:lang/locallang_common.xml:enable',
			'canNotCollapse' => 1,
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
				'foreign_table' => 'tx_appointments_domain_model_type',
				'foreign_table_where' => 'AND tx_appointments_domain_model_type.pid=###CURRENT_PID### AND tx_appointments_domain_model_type.sys_language_uid IN (-1,0)',
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
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'superuser_only' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.superuser_only',
			'config' => array(
				'type' => 'check',
			),
		),
		'exclusive_availability' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.exclusive_availability',
			'config' => array(
				'type' => 'check',
			),
		),
		'dont_block_types' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.dont_block_types',
			'config' => array(
				'type' => 'check',
			),
		),
		'dont_restrict_type_counts' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.dont_restrict_type_counts',
			'config' => array(
				'type' => 'check',
			),
		),
		'default_duration' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.default_duration',
			'config' => $appointments_type_configDefDur,
		),
		'start_time_monday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time', #@LOW can't differentiate between these fields in advanced TCA editing, because of same label
			'config' => $appointments_type_configStartTime,
		),
		'start_time_tuesday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
			'config' => $appointments_type_configStartTime,
		),
		'start_time_wednesday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
			'config' => $appointments_type_configStartTime,
		),
		'start_time_thursday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
			'config' => $appointments_type_configStartTime,
		),
		'start_time_friday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
			'config' => $appointments_type_configStartTime,
		),
		'start_time_saturday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
			'config' => $appointments_type_configStartTime,
		),
		'start_time_sunday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
			'config' => $appointments_type_configStartTime,
		),
		'stop_time_monday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
			'config' => $appointments_type_configStopTime,
		),
		'stop_time_tuesday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
			'config' => $appointments_type_configStopTime,
		),
		'stop_time_wednesday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
			'config' => $appointments_type_configStopTime,
		),
		'stop_time_thursday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
			'config' => $appointments_type_configStopTime,
		),
		'stop_time_friday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
			'config' => $appointments_type_configStopTime,
		),
		'stop_time_saturday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
			'config' => $appointments_type_configStopTime,
		),
		'stop_time_sunday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
			'config' => $appointments_type_configStopTime,
		),
		'exclude_holidays' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.exclude_holidays',
			'config' => array(
				'type' => 'check',
				'default' => 1
			),
		),
		'max_amount_monday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
			'config' => $appointments_type_configMaxAmount,
		),
		'max_amount_tuesday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
			'config' => $appointments_type_configMaxAmount,
		),
		'max_amount_wednesday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
			'config' => $appointments_type_configMaxAmount,
		),
		'max_amount_thursday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
			'config' => $appointments_type_configMaxAmount,
		),
		'max_amount_friday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
			'config' => $appointments_type_configMaxAmount,
		),
		'max_amount_saturday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
			'config' => $appointments_type_configMaxAmount,
		),
		'max_amount_sunday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
			'config' => $appointments_type_configMaxAmount,
		),
		'minute_interval_monday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
			'config' => $appointments_type_configMinInt,
		),
		'minute_interval_tuesday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
			'config' => $appointments_type_configMinInt,
		),
		'minute_interval_wednesday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
			'config' => $appointments_type_configMinInt,
		),
		'minute_interval_thursday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
			'config' => $appointments_type_configMinInt,
		),
		'minute_interval_friday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
			'config' => $appointments_type_configMinInt,
		),
		'minute_interval_saturday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
			'config' => $appointments_type_configMinInt,
		),
		'minute_interval_sunday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
			'config' => $appointments_type_configMinInt,
		),
		'max_amount_per_var_days' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount_per_var_days',
			'config' => array(
				'type' => 'input',
				'size' => 5,
				'max' => 5,
				'range' => array(
					'lower' => 0,
					'upper' => 10080
				),
				'eval' => 'int'
			),
		),
		'per_var_days' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.per_var_days',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'max' => 1,
				'range' => array(
					'lower' => 0,
					'upper' => 7
				),
				'eval' => 'int'
			),
		),
		'per_var_days_interval' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.per_var_days_interval',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'max' => 2,
				'range' => array(
					'lower' => 0,
					'upper' => 24
				),
				'eval' => 'int'
			),
		),
		'between_minutes' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.between_minutes',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			),
		),
		'hours_mutable' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.hours_mutable',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			),
		),
		'blocked_hours' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.blocked_hours',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			),
		),
		'blocked_hours_workdays' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.blocked_hours_workdays',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			),
		),
		'max_days_forward' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_days_forward',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			),
		),
		'form_fields' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.form_fields',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_appointments_domain_model_formfield',
				'foreign_field' => 'type',
				'maxitems'      => 9999,
				'appearance' => array(
					'collapseAll' => 1,
					'levelLinksPosition' => 'top',
					'useSortable' => 1,
					'showSynchronizationLink' => 1,
					'showPossibleLocalizationRecords' => 1,
					'showAllLocalizationLink' => 1
				),
			),
		),
		'address_disable' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.address_disable',
			'config' => array(
				'type' => 'check',
			),
		),
		'address_enable_name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.name',
			'config' => array(
				'type' => 'check',
				'default' => 1
			),
		),
		'address_enable_gender' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.gender',
			'config' => array(
				'type' => 'check',
				'default' => 1
			),
		),
		'address_enable_birthday' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.birthday',
			'config' => array(
				'type' => 'check',
				'default' => 1
			),
		),
		'address_enable_address' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.address',
			'config' => array(
				'type' => 'check',
				'default' => 1
			),
		),
		'address_enable_security' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.social_security_number',
			'config' => array(
				'type' => 'check',
				'default' => 1
			),
		),
		'address_enable_email' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.email',
			'config' => array(
				'type' => 'check',
				'default' => 1
			),
		),
	),
];