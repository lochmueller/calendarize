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
        'editlock' => 'editlock',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'title' => $ll . 'tx_calendarize_domain_model_pluginconfiguration',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'dividers2tabs' => true,
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'searchFields' => 'title,model_name,configuration,storage_pid,recursive,detail_pid,list_pid,year_pid,
            quarter_pid,month_pid,week_pid,day_pid,booking_pid',
        'iconfile' => 'EXT:calendarize/Resources/Public/Icons/PluginConfiguration.png',
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
                        0 => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
                        1 => -1,
                    ],
                    1 => [
                        0 => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
                        1 => -2,
                    ],
                    2 => [
                        0 => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
                        1 => '--div--',
                    ],
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
            ],
        ],
        'editlock' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:editlock',
            'config' => [
                'type' => 'check',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
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
                        0 => '',
                        1 => 0,
                    ],
                ],
                'foreign_table' => 'tx_calendarize_domain_model_pluginconfiguration',
                'foreign_table_where' => 'AND tx_calendarize_domain_model_pluginconfiguration.pid=###CURRENT_PID### AND tx_calendarize_domain_model_pluginconfiguration.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'title' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.title',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'model_name' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.model_name',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    0 => [
                        0 => 'Default',
                        1 => 'HDNET\\Calendarize\\Domain\\Model\\PluginConfiguration',
                    ],
                ],
            ],
        ],
        'configuration' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.configuration',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectCheckBox',
                'renderMode' => 'checkbox',
                'itemsProcFunc' => 'HDNET\\Calendarize\\Service\\PluginConfigurationService->addConfig',
                'minitems' => 1,
                'maxitems' => 99,
            ],
        ],
        'storage_pid' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.storage_pid',
            'config' => [
                'type' => 'group',
                'allowed' => 'pages',
                'size' => 3,
                'maxitems' => 50,
                'minitems' => 0,
            ],
        ],
        'recursive' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.recursive',
            'config' => [
                'type' => 'select',
                'size' => 1,
                'renderType' => 'selectSingle',
                'items' => [
                    0 => [
                        0 => $ll . 'inherit',
                        1 => '',
                    ],
                    1 => [
                        0 => $ll . 'recursive.I.0',
                        1 => '0',
                    ],
                    2 => [
                        0 => $ll . 'recursive.I.1',
                        1 => '1',
                    ],
                    3 => [
                        0 => $ll . 'recursive.I.2',
                        1 => '2',
                    ],
                    4 => [
                        0 => $ll . 'recursive.I.3',
                        1 => '3',
                    ],
                    5 => [
                        0 => $ll . 'recursive.I.4',
                        1 => '4',
                    ],
                    6 => [
                        0 => $ll . 'recursive.I.5',
                        1 => '250',
                    ],
                ],
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'detail_pid' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.detail_pid',
            'config' => [
                'type' => 'group',
                'eval' => 'int',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'list_pid' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.list_pid',
            'config' => [
                'type' => 'group',
                'eval' => 'int',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'year_pid' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.year_pid',
            'config' => [
                'type' => 'group',
                'eval' => 'int',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'quarter_pid' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.quarter_pid',
            'config' => [
                'type' => 'group',
                'eval' => 'int',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'month_pid' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.month_pid',
            'config' => [
                'type' => 'group',
                'eval' => 'int',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'week_pid' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.week_pid',
            'config' => [
                'type' => 'group',
                'eval' => 'int',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'day_pid' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.day_pid',
            'config' => [
                'type' => 'group',
                'eval' => 'int',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'booking_pid' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.booking_pid',
            'config' => [
                'type' => 'group',
                'eval' => 'int',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'categories' => [
            'config' => [
                'type' => 'category',
                'foreign_table' => 'sys_category',
                'size' => 20,
                'foreign_table_where' => ' AND {#sys_category}.{#sys_language_uid} IN (-1, 0)',
                'relationship' => 'manyToMany',
                'maxitems' => 99999,
                'default' => 0,
                'MM' => 'sys_category_record_mm',
                'MM_opposite_field' => 'items',
                'MM_match_fields' => [
                    'tablenames' => 'tx_calendarize_domain_model_pluginconfiguration',
                    'fieldname' => 'categories',
                ],
            ],
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
            'exclude' => true,
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
                title,model_name,configuration,
                --div--;PID,detail_pid,list_pid,year_pid,quarter_pid,month_pid,week_pid,day_pid,booking_pid,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
            ',
        ],
    ],
];
