<?php

defined('TYPO3') or die();

$appointments_type_configStartTime = [
    #@LOW add custom regex eval e.g. /([0-1]{1}[0-9]{1}|2[0-3]{1}):[0-5]{1}[0-9{1}/, see manual TCA->input->eval on how
    'type' => 'input',
    'size' => 4,
    'max' => 5,
    'default' => '08:00',
];
$appointments_type_configStopTime = $appointments_type_configStartTime;
$appointments_type_configStopTime['default'] = '20:00';
$appointments_type_configMaxAmount = [
    'type' => 'number',
    'size' => 4,
    'max' => 4,
    'range' => [
        'lower' => 0,
        'upper' => 1440,
    ],
];
$appointments_type_configDefDur = $appointments_type_configMaxAmount;
$appointments_type_configMinInt = $appointments_type_configDefDur;
$appointments_type_configMinInt['range']['lower'] = 1;
$appointments_type_configMinInt['default'] = 15;
$appointments_type_configMinInt['required'] = true;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'dividers2tabs' => true,
        'sortby' => 'sorting',
        'versioningWS' => true,
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
        'iconfile' => 'EXT:appointments/Resources/Public/Icons/tx_appointments_domain_model_type.gif',
    ],
    'types' => [
        '0' => [
            'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, superuser_only, exclusive_availability, dont_block_types, dont_restrict_type_counts, default_duration, between_minutes, max_days_forward, hours_mutable, --palette--;;b_hours,
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
				--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime, endtime',
        ],
    ],
    'palettes' => [
        'b_hours' => [
            'showitem' => 'blocked_hours, --linebreak--, blocked_hours_workdays',
        ],
        'monday' => [
            'showitem' => 'max_amount_monday, start_time_monday, stop_time_monday, minute_interval_monday',
        ],
        'tuesday' => [
            'showitem' => 'max_amount_tuesday, start_time_tuesday, stop_time_tuesday, minute_interval_tuesday',
        ],
        'wednesday' => [
            'showitem' => 'max_amount_wednesday, start_time_wednesday, stop_time_wednesday, minute_interval_wednesday',
        ],
        'thursday' => [
            'showitem' => 'max_amount_thursday, start_time_thursday, stop_time_thursday, minute_interval_thursday',
        ],
        'friday' => [
            'showitem' => 'max_amount_friday, start_time_friday, stop_time_friday, minute_interval_friday',
        ],
        'saturday' => [
            'showitem' => 'max_amount_saturday, start_time_saturday, stop_time_saturday, minute_interval_saturday',
        ],
        'sunday' => [
            'showitem' => 'max_amount_sunday, start_time_sunday, stop_time_sunday, minute_interval_sunday',
        ],
        'overrule' => [
            'showitem' => 'max_amount_per_var_days, per_var_days, per_var_days_interval, --linebreak--, exclude_holidays',
        ],
        #@TODO __CSH
        'address_name' => [
            'showitem' => 'address_enable_name;LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:enable',
        ],
        'address_gender' => [
            'showitem' => 'address_enable_gender;LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:enable',
        ],
        'address_birthday' => [
            'showitem' => 'address_enable_birthday;LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:enable',
        ],
        'address_address' => [
            'showitem' => 'address_enable_address;LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:enable',
        ],
        'address_security' => [
            'showitem' => 'address_enable_security;LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:enable',
        ],
        'address_email' => [
            'showitem' => 'address_enable_email;LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:enable',
        ],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'foreign_table' => 'sys_category',
                'foreign_table_where' => 'AND sys_category.uid=###REC_FIELD_l10n_parent### AND sys_category.sys_language_uid IN (-1,0)',
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => '',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'superuser_only' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.superuser_only',
            'config' => [
                'type' => 'check',
            ],
        ],
        'exclusive_availability' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.exclusive_availability',
            'config' => [
                'type' => 'check',
            ],
        ],
        'dont_block_types' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.dont_block_types',
            'config' => [
                'type' => 'check',
            ],
        ],
        'dont_restrict_type_counts' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.dont_restrict_type_counts',
            'config' => [
                'type' => 'check',
            ],
        ],
        'default_duration' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.default_duration',
            'config' => $appointments_type_configDefDur,
        ],
        'start_time_monday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time', #@LOW can't differentiate between these fields in advanced TCA editing, because of same label
            'config' => $appointments_type_configStartTime,
        ],
        'start_time_tuesday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
            'config' => $appointments_type_configStartTime,
        ],
        'start_time_wednesday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
            'config' => $appointments_type_configStartTime,
        ],
        'start_time_thursday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
            'config' => $appointments_type_configStartTime,
        ],
        'start_time_friday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
            'config' => $appointments_type_configStartTime,
        ],
        'start_time_saturday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
            'config' => $appointments_type_configStartTime,
        ],
        'start_time_sunday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.start_time',
            'config' => $appointments_type_configStartTime,
        ],
        'stop_time_monday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
            'config' => $appointments_type_configStopTime,
        ],
        'stop_time_tuesday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
            'config' => $appointments_type_configStopTime,
        ],
        'stop_time_wednesday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
            'config' => $appointments_type_configStopTime,
        ],
        'stop_time_thursday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
            'config' => $appointments_type_configStopTime,
        ],
        'stop_time_friday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
            'config' => $appointments_type_configStopTime,
        ],
        'stop_time_saturday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
            'config' => $appointments_type_configStopTime,
        ],
        'stop_time_sunday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.stop_time',
            'config' => $appointments_type_configStopTime,
        ],
        'exclude_holidays' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.exclude_holidays',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ],
        ],
        'max_amount_monday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
            'config' => $appointments_type_configMaxAmount,
        ],
        'max_amount_tuesday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
            'config' => $appointments_type_configMaxAmount,
        ],
        'max_amount_wednesday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
            'config' => $appointments_type_configMaxAmount,
        ],
        'max_amount_thursday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
            'config' => $appointments_type_configMaxAmount,
        ],
        'max_amount_friday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
            'config' => $appointments_type_configMaxAmount,
        ],
        'max_amount_saturday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
            'config' => $appointments_type_configMaxAmount,
        ],
        'max_amount_sunday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount',
            'config' => $appointments_type_configMaxAmount,
        ],
        'minute_interval_monday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
            'config' => $appointments_type_configMinInt,
        ],
        'minute_interval_tuesday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
            'config' => $appointments_type_configMinInt,
        ],
        'minute_interval_wednesday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
            'config' => $appointments_type_configMinInt,
        ],
        'minute_interval_thursday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
            'config' => $appointments_type_configMinInt,
        ],
        'minute_interval_friday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
            'config' => $appointments_type_configMinInt,
        ],
        'minute_interval_saturday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
            'config' => $appointments_type_configMinInt,
        ],
        'minute_interval_sunday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.minute_interval',
            'config' => $appointments_type_configMinInt,
        ],
        'max_amount_per_var_days' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_amount_per_var_days',
            'config' => [
                'type' => 'number',
                'size' => 5,
                'max' => 5,
                'range' => [
                    'lower' => 0,
                    'upper' => 10080,
                ],
            ],
        ],
        'per_var_days' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.per_var_days',
            'config' => [
                'type' => 'number',
                'size' => 4,
                'max' => 1,
                'range' => [
                    'lower' => 0,
                    'upper' => 7,
                ],
            ],
        ],
        'per_var_days_interval' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.per_var_days_interval',
            'config' => [
                'type' => 'number',
                'size' => 4,
                'max' => 2,
                'range' => [
                    'lower' => 0,
                    'upper' => 24,
                ],
            ],
        ],
        'between_minutes' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.between_minutes',
            'config' => [
                'type' => 'number',
                'size' => 4,
            ],
        ],
        'hours_mutable' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.hours_mutable',
            'config' => [
                'type' => 'number',
                'size' => 4,
            ],
        ],
        'blocked_hours' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.blocked_hours',
            'config' => [
                'type' => 'number',
                'size' => 4,
            ],
        ],
        'blocked_hours_workdays' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.blocked_hours_workdays',
            'config' => [
                'type' => 'number',
                'size' => 4,
            ],
        ],
        'max_days_forward' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.max_days_forward',
            'config' => [
                'type' => 'number',
                'size' => 4,
            ],
        ],
        'form_fields' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.form_fields',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_appointments_domain_model_formfield',
                'foreign_field' => 'type',
                'maxitems' => 9999,
                'appearance' => [
                    'collapseAll' => 1,
                    'levelLinksPosition' => 'top',
                    'useSortable' => 1,
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'address_disable' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_type.address_disable',
            'config' => [
                'type' => 'check',
            ],
        ],
        'address_enable_name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.name',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ],
        ],
        'address_enable_gender' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.gender',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ],
        ],
        'address_enable_birthday' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.birthday',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ],
        ],
        'address_enable_address' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.address',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ],
        ],
        'address_enable_security' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.social_security_number',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ],
        ],
        'address_enable_email' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_address.email',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ],
        ],
    ],
];
