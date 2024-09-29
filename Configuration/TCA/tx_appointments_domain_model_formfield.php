<?php

defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'dividers2tabs' => true,
        'sortby' => 'sorting',
        'versioningWS' => true,
        'hideTable' => true,
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
        'iconfile' => 'EXT:appointments/Resources/Public/Icons/tx_appointments_domain_model_formfield.gif',
    ],
    'types' => [
        '0' => [
            'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, label, csh, field_type, choices, function, validation_types,
				--palette--;LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.palette.enable_field;enable_field,
				--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime, endtime',
        ],
    ],
    'palettes' => [
        'enable_field' => [
            'showitem' => 'enable_field, enable_value',
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
                    ['', 0],
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
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'label' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.label',
            'config' => [
                'type' => 'text',
                'cols' => 48,
                'rows' => 2,
                'eval' => 'trim,required',
            ],
        ],
        'csh' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.csh',
            'config' => [
                'type' => 'text',
                'cols' => 48,
                'rows' => 5,
                'eval' => 'trim',
            ],
        ],
        'validation_types' => [
            'exclude' => 0,
            'displayCond' => 'FIELD:field_type:!=:' . \Innologi\Appointments\Domain\Model\FormField::TYPE_BOOLEAN,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'items' => [
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.datetime', \Innologi\Appointments\Domain\Model\FormField::VALIDATE_DATE_TIME],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.email', \Innologi\Appointments\Domain\Model\FormField::VALIDATE_EMAIL_ADDRESS],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.float', \Innologi\Appointments\Domain\Model\FormField::VALIDATE_FLOAT],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.integer', \Innologi\Appointments\Domain\Model\FormField::VALIDATE_INTEGER],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.notempty', \Innologi\Appointments\Domain\Model\FormField::VALIDATE_NOT_EMPTY],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.number', \Innologi\Appointments\Domain\Model\FormField::VALIDATE_NUMBER],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.validation_types.naturalnumber', \Innologi\Appointments\Domain\Model\FormField::VALIDATE_NATURALNUMBER],
                ],
                'size' => 7,
                'maxitems' => 99,
                'multiple' => 0,
            ],
        ],
        'field_type' => [
            'exclude' => 0,
            'onChange' => 'reload',
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type.boolean', \Innologi\Appointments\Domain\Model\FormField::TYPE_BOOLEAN],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type.radio', \Innologi\Appointments\Domain\Model\FormField::TYPE_RADIO],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type.select', \Innologi\Appointments\Domain\Model\FormField::TYPE_SELECT],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type.textsmall', \Innologi\Appointments\Domain\Model\FormField::TYPE_TEXTSMALL],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.field_type.textlarge', \Innologi\Appointments\Domain\Model\FormField::TYPE_TEXTLARGE],
                ],
                'size' => 1,
                'maxitems' => 1,
                'default' => \Innologi\Appointments\Domain\Model\FormField::TYPE_TEXTSMALL,
            ],
        ],
        'choices' => [
            'exclude' => 0,
            'displayCond' => 'FIELD:field_type:IN:' . \Innologi\Appointments\Domain\Model\FormField::TYPE_SELECT . ',' . \Innologi\Appointments\Domain\Model\FormField::TYPE_RADIO,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.choices',
            'config' => [
                'type' => 'text',
                'cols' => 48,
                'rows' => 5,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'function' => [
            'exclude' => 0,
            'displayCond' => 'FIELD:field_type:IN:' . \Innologi\Appointments\Domain\Model\FormField::TYPE_TEXTLARGE . ',' . \Innologi\Appointments\Domain\Model\FormField::TYPE_TEXTSMALL,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.function',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.function.info', \Innologi\Appointments\Domain\Model\FormField::FUNCTION_INFORMATIONAL],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.function.addtime', \Innologi\Appointments\Domain\Model\FormField::FUNCTION_ADDTIME],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.function.email', \Innologi\Appointments\Domain\Model\FormField::FUNCTION_EMAIL],
                ],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'enable_field' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.enable_field',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.enable_field.default', 0],
                    ['LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.div.enable_field', '--div--'],
                ],
                'foreign_table' => 'tx_appointments_domain_model_formfield',
                /*
                 * only allow formfields other than this, that are of the same type,
                 * and have select as field_type. But also don't allow any chaining
                 * as it is currently not supported: don't allow fields enabled by
                 * other fields, or ANY field if current one is set as enable_field
                 * for another field.
                 */
                // @TODO be sure to check indexes
                // @LOW Add support for all field types, eventually
                // Note that T3 8.7.4 fails to extract the "ORDER BY" before putting it in a "WHERE", if there are newlines and possibly tabs, due to the limited regular expession employed in \TYPO3\CMS\Backend\Form\FormDataProvider\AbstractItemProvider->processForeignTableClause():1145
                'foreign_table_where' => 'AND tx_appointments_domain_model_formfield.uid <> ###THIS_UID### AND tx_appointments_domain_model_formfield.type = ###REC_FIELD_type### AND tx_appointments_domain_model_formfield.field_type IN(' . \Innologi\Appointments\Domain\Model\FormField::TYPE_SELECT . ',' . \Innologi\Appointments\Domain\Model\FormField::TYPE_RADIO . ') AND tx_appointments_domain_model_formfield.enable_field = 0 AND 0=(SELECT COUNT(*) FROM tx_appointments_domain_model_formfield WHERE enable_field = ###THIS_UID###) ORDER BY tx_appointments_domain_model_formfield.sorting ASC',
                // 'MM' => 'tx_appointments_formfield_mm', // @LOW allow multiple choices?
                'size' => 1,
                // 'autoSizeMax' => 30,
                'minitems' => 0,
                'maxitems' => 1,
                'multiple' => 0,
                'default' => 0,
            ],
        ],
        'enable_value' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:appointments/Resources/Private/Language/locallang_db.xml:tx_appointments_domain_model_formfield.enable_value',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim',
            ],
        ],
        'type' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        // we need this to be available in our model objects, and with the replaced persistence mapping feature, this does not work without a TCA definition
        'sorting' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];
