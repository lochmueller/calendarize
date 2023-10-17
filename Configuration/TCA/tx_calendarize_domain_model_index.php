<?php

declare(strict_types=1);

$ll = 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:';

return [
    'ctrl' => [
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'title' => $ll . 'tx_calendarize_domain_model_index',
        'label' => 'unique_register_key',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'searchFields' => 'unique_register_key,foreign_table,foreign_uid,start_date,end_date,start_time,end_time,
            all_day,open_end_time,state,slug',
        'iconfile' => 'EXT:calendarize/Resources/Public/Icons/Index.png',
        'hideTable' => true,
        'rootLevel' => -1,
        'label_alt' => 'start_date',
        'label_alt_force' => true,
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'columns' => [
        'fe_group' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 5,
                'maxitems' => 20,
                'items' => [
                    0 => [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
                        'value' => -1,
                    ],
                    1 => [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
                        'value' => -2,
                    ],
                    2 => [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
                        'value' => '--div--',
                    ],
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
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
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'range' => [
                    'upper' => 2145913200,
                ],
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'sys_language_uid' => [
            'exclude' => 1,
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
                'default' => 0,
                'items' => [
                    0 => [
                        'label' => '',
                        'value' => 0,
                    ],
                ],
                'foreign_table' => 'tx_calendarize_domain_model_index',
                'foreign_table_where' => 'AND tx_calendarize_domain_model_index.pid=###CURRENT_PID### AND tx_calendarize_domain_model_index.sys_language_uid IN (-1,0)',
            ],
        ],
        'unique_register_key' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_index.unique_register_key',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'foreign_table' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_index.foreign_table',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'foreign_uid' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_index.foreign_uid',
            'config' => [
                'type' => 'number',
                'size' => 10,
                'readOnly' => true,
            ],
        ],
        'start_date' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_index.start_date',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
                'dbType' => 'date',
                'format' => 'date',
            ],
        ],
        'end_date' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_index.end_date',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
                'dbType' => 'date',
                'format' => 'date',
            ],
        ],
        'start_time' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_index.start_time',
            'config' => [
                'type' => 'datetime',
                'size' => 10,
                'readOnly' => true,
                'format' => 'time',
            ],
        ],
        'end_time' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_index.end_time',
            'config' => [
                'type' => 'datetime',
                'size' => 10,
                'readOnly' => true,
                'format' => 'time',
            ],
        ],
        'all_day' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_index.all_day',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
            ],
        ],
        'open_end_time' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_index.open_end_time',
            'config' => [
                'type' => 'check',
            ],
        ],
        'state' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_index.state',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'slug' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_index.slug',
            'config' => [
                'type' => 'slug',
                'prependSlash' => false,
                'generatorOptions' => [
                    'fields' => [
                    ],
                    'prefixParentPageSlug' => true,
                ],
                'fallbackCharacter' => '-',
                'eval' => 'unique',
            ],
        ],
        't3_origuid' => [
            'config' => [
                'type' => 'passthrough',
                'default' => 0,
            ],
        ],
    ],
    'palettes' => [
        'language' => [
            'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource',
        ],
        'access' => [
            'showitem' => 'starttime, endtime, --linebreak--, hidden, editlock, --linebreak--, fe_group',
        ],
    ],
    'types' => [
        1 => [
            'showitem' => '
                unique_register_key,foreign_table,foreign_uid,start_date,end_date,start_time,end_time,
                all_day,open_end_time,state,slug,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
            ',
        ],
    ],
];
