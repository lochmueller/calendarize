<?php

declare(strict_types=1);

$ll = 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_calendarize_domain_model_pluginconfiguration',
        'label' => 'title',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'editlock' => 'editlock',
        'iconfile' => 'EXT:calendarize/Resources/Public/Icons/PluginConfiguration.png',
        'searchFields' => 'title,model_name,configuration,storage_pid,recursive,detail_pid,list_pid,year_pid,
            quarter_pid,month_pid,week_pid,day_pid,booking_pid',
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
        'editlock' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:editlock',
            'config' => [
                'type' => 'check',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'hidden' => [
            'exclude' => true,
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
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
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
                'default' => 0,
                'items' => [
                    0 => [
                        'label' => '',
                        'value' => 0,
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
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.title',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'model_name' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.model_name',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    0 => [
                        'label' => 'Default',
                        'value' => HDNET\Calendarize\Domain\Model\PluginConfiguration::class,
                    ],
                ],
            ],
        ],
        'configuration' => [
            'exclude' => true,
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
            'exclude' => true,
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
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.recursive',
            'config' => [
                'type' => 'select',
                'size' => 1,
                'renderType' => 'selectSingle',
                'items' => [
                    0 => [
                        'label' => $ll . 'inherit',
                        'value' => '',
                    ],
                    1 => [
                        'label' => $ll . 'recursive.I.0',
                        'value' => '0',
                    ],
                    2 => [
                        'label' => $ll . 'recursive.I.1',
                        'value' => '1',
                    ],
                    3 => [
                        'label' => $ll . 'recursive.I.2',
                        'value' => '2',
                    ],
                    4 => [
                        'label' => $ll . 'recursive.I.3',
                        'value' => '3',
                    ],
                    5 => [
                        'label' => $ll . 'recursive.I.4',
                        'value' => '4',
                    ],
                    6 => [
                        'label' => $ll . 'recursive.I.5',
                        'value' => '250',
                    ],
                ],
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'detail_pid' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.detail_pid',
            'config' => [
                'type' => 'group',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'list_pid' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.list_pid',
            'config' => [
                'type' => 'group',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'year_pid' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.year_pid',
            'config' => [
                'type' => 'group',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'quarter_pid' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.quarter_pid',
            'config' => [
                'type' => 'group',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'month_pid' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.month_pid',
            'config' => [
                'type' => 'group',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'week_pid' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.week_pid',
            'config' => [
                'type' => 'group',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'day_pid' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.day_pid',
            'config' => [
                'type' => 'group',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'booking_pid' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_pluginconfiguration.booking_pid',
            'config' => [
                'type' => 'group',
                'size' => 1,
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'categories' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
            'config' => [
                'type' => 'category',
            ],
        ],
    ],
    'palettes' => [
        'language' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.language',
            'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource',
        ],
        'access' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access',
            'showitem' => 'starttime, endtime, --linebreak--, hidden, editlock, --linebreak--, fe_group',
        ],
    ],
    'types' => [
        1 => [
            'showitem' => '
                title,model_name,configuration,
                --div--;PID,detail_pid,list_pid,year_pid,quarter_pid,month_pid,week_pid,day_pid,booking_pid,
                --div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;access,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
            ',
        ],
    ],
];
