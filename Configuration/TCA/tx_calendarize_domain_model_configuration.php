<?php

declare(strict_types=1);

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Service\TcaService;

$ll = 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_calendarize_domain_model_configuration',
        'label' => 'type',
        'formattedLabel_userFunc' => TcaService::class . '->configurationTitle',
        'hideTable' => true,
        'type' => 'type',
        'languageField' => 'sys_language_uid',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'transOrigPointerField' => 'l10n_parent',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
            'starttime' => 'starttime',
        ],
        'editlock' => 'editlock',
        'iconfile' => 'EXT:calendarize/Resources/Public/Icons/Configuration.png',
        'typeicon_column' => 'type',
        'typeicon_classes' => [
            Configuration::TYPE_EXTERNAL => 'apps-calendarize-type-' . Configuration::TYPE_EXTERNAL,
            Configuration::TYPE_GROUP => 'apps-calendarize-type-' . Configuration::TYPE_GROUP,
            Configuration::TYPE_TIME => 'apps-calendarize-type-' . Configuration::TYPE_TIME,
        ],
        'searchFields' => 'type,handling,state,start_date,end_date,end_date_dynamic,start_time,end_time,all_day,
            open_end_time,external_ics_url,groups,frequency,till_date,till_days,till_days_relative,till_days_past,
            counter_amount,counter_interval,recurrence,day,import_id',
        'security' => [
            'ignorePageTypeRestriction' => true,
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
                    [
                        'label' => '',
                        'value' => 0,
                    ],
                ],
                'foreign_table' => 'tx_calendarize_domain_model_configuration',
                'foreign_table_where' => 'AND {#tx_calendarize_domain_model_configuration}.{#pid}=###CURRENT_PID###'
                    . ' AND {#tx_calendarize_domain_model_configuration}.{#sys_language_uid} IN (-1,0)',
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => '',
            ],
        ],
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
        'type' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => $ll . 'configuration.type.time', 'value' => 'time'],
                    ['label' => $ll . 'configuration.type.group', 'value' => 'group'],
                    ['label' => $ll . 'configuration.type.external', 'value' => 'external'],
                ],
                'default' => Configuration::TYPE_TIME,
            ],
        ],
        'handling' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.handling',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => $ll . 'configuration.handling.' . Configuration::HANDLING_INCLUDE,
                        'value' => Configuration::HANDLING_INCLUDE,
                    ],
                    [
                        'label' => $ll . 'configuration.handling.' . Configuration::HANDLING_EXCLUDE,
                        'value' => Configuration::HANDLING_EXCLUDE,
                    ],
                    [
                        'label' => $ll . 'configuration.handling.' . Configuration::HANDLING_OVERRIDE,
                        'value' => Configuration::HANDLING_OVERRIDE,
                    ],
                    [
                        'label' => $ll . 'configuration.handling.' . Configuration::HANDLING_CUTOUT,
                        'value' => Configuration::HANDLING_CUTOUT,
                    ],
                ],
                'default' => Configuration::HANDLING_INCLUDE,
            ],
        ],
        'state' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.state',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => '',
                        'value' => Configuration::STATE_DEFAULT,
                    ],
                    [
                        'label' => $ll . 'configuration.state.canceled',
                        'value' => Configuration::STATE_CANCELED,
                    ],
                ],
                'default' => Configuration::STATE_DEFAULT,
            ],
        ],
        'start_date' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.start_date',
            'config' => [
                'type' => 'datetime',
                'format' => 'date',
                'required' => true,
                'dbType' => 'date',
                'size' => 13,
            ],
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
        ],
        'end_date' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.end_date',
            'config' => [
                'type' => 'datetime',
                'format' => 'date',
                'dbType' => 'date',
                'size' => 13,
            ],
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
        ],
        'start_time' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.start_time',
            'config' => [
                'type' => 'datetime',
                'format' => 'time',
                'required' => true,
                'default' => 0,
                'size' => 10,
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:all_day:!=:1',
                    'FIELD:type:=:' . Configuration::TYPE_TIME,
                ],
            ],
        ],
        'end_time' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.end_time',
            'config' => [
                'type' => 'datetime',
                'format' => 'time',
                'default' => 0,
                'size' => 10,
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:all_day:!=:1',
                    'FIELD:open_end_time:!=:1',
                    'FIELD:type:=:' . Configuration::TYPE_TIME,
                ],
            ],
        ],
        'all_day' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.all_day',
            'onChange' => 'reload',
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'open_end_time' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.open_end_time',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:all_day:!=:1',
                    'FIELD:type:=:' . Configuration::TYPE_TIME,
                ],
            ],
        ],
        'groups' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.groups',
            'exclude' => true,
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_calendarize_domain_model_configurationgroup',
                'minitems' => 1,
                'size' => 5,
                'maxitems' => 99,
            ],
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_GROUP,
        ],
        'frequency' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.frequency',
            'exclude' => true,
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => $ll . 'configuration.frequency.' . Configuration::FREQUENCY_NONE,
                        'value' => Configuration::FREQUENCY_NONE,
                    ],
                    [
                        'label' => $ll . 'configuration.frequency.' . Configuration::FREQUENCY_MINUTELY,
                        'value' => Configuration::FREQUENCY_MINUTELY,
                    ],
                    [
                        'label' => $ll . 'configuration.frequency.' . Configuration::FREQUENCY_HOURLY,
                        'value' => Configuration::FREQUENCY_HOURLY,
                    ],
                    [
                        'label' => $ll . 'configuration.frequency.' . Configuration::FREQUENCY_DAILY,
                        'value' => Configuration::FREQUENCY_DAILY,
                    ],
                    [
                        'label' => $ll . 'configuration.frequency.' . Configuration::FREQUENCY_WEEKLY,
                        'value' => Configuration::FREQUENCY_WEEKLY,
                    ],
                    [
                        'label' => $ll . 'configuration.frequency.' . Configuration::FREQUENCY_MONTHLY,
                        'value' => Configuration::FREQUENCY_MONTHLY,
                    ],
                    [
                        'label' => $ll . 'configuration.frequency.' . Configuration::FREQUENCY_YEARLY,
                        'value' => Configuration::FREQUENCY_YEARLY,
                    ],
                ],
                'default' => Configuration::FREQUENCY_NONE,
            ],
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
        ],
        'till_date' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.till_date',
            'exclude' => true,
            'config' => [
                'type' => 'datetime',
                'format' => 'date',
                'dbType' => 'date',
                'size' => 13,
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:frequency:!=:' . Configuration::FREQUENCY_NONE,
                    'FIELD:type:=:' . Configuration::TYPE_TIME,
                ],
            ],
        ],
        'till_days' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.till_days',
            'exclude' => true,
            'config' => [
                'type' => 'number',
                'nullable' => true,
                'default' => null,
                'size' => 10,
                'range' => [
                    'lower' => 1,
                ],
            ],
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
        ],
        'till_days_relative' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.till_days_relative',
            'exclude' => true,
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
        ],
        'till_days_past' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.till_days_past',
            'exclude' => true,
            'config' => [
                'type' => 'number',
                'nullable' => true,
                'default' => null,
                'size' => 10,
                'range' => [
                    'lower' => 0,
                ],
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:type:=:' . Configuration::TYPE_TIME,
                    'FIELD:till_days_relative:=:1',
                ],
            ],
        ],
        'counter_amount' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.counter_amount',
            'exclude' => true,
            'config' => [
                'type' => 'number',
                'size' => 10,
                'default' => 0,
                'range' => [
                    'lower' => 0,
                ],
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:frequency:!=:' . Configuration::FREQUENCY_NONE,
                    'FIELD:type:=:' . Configuration::TYPE_TIME,
                ],
            ],
        ],
        'counter_interval' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.counter_interval',
            'exclude' => true,
            'config' => [
                'type' => 'number',
                'required' => true,
                'size' => 10,
                'default' => 1,
                'range' => [
                    'lower' => 1,
                ],
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:frequency:!=:' . Configuration::FREQUENCY_NONE,
                    'FIELD:type:=:' . Configuration::TYPE_TIME,
                ],
            ],
        ],
        'external_ics_url' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.external_ics_url',
            'exclude' => true,
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_EXTERNAL,
            'config' => [
                'type' => 'link',
                'required' => true,
                'allowedTypes' => ['file', 'url'],
                'appearance' => [
                    'allowedFileExtensions' => ['ics'],
                    'allowedOptions' => [],
                ],
            ],
        ],
        'day' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.day',
            'exclude' => true,
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingleBox',
                'items' => [
                    [
                        'label' => $ll . 'configuration.day.' . Configuration::DAY_MONDAY,
                        'value' => Configuration::DAY_MONDAY,
                    ],
                    [
                        'label' => $ll . 'configuration.day.' . Configuration::DAY_TUESDAY,
                        'value' => Configuration::DAY_TUESDAY,
                    ],
                    [
                        'label' => $ll . 'configuration.day.' . Configuration::DAY_WEDNESDAY,
                        'value' => Configuration::DAY_WEDNESDAY,
                    ],
                    [
                        'label' => $ll . 'configuration.day.' . Configuration::DAY_THURSDAY,
                        'value' => Configuration::DAY_THURSDAY,
                    ],
                    [
                        'label' => $ll . 'configuration.day.' . Configuration::DAY_FRIDAY,
                        'value' => Configuration::DAY_FRIDAY,
                    ],
                    [
                        'label' => $ll . 'configuration.day.' . Configuration::DAY_SATURDAY,
                        'value' => Configuration::DAY_SATURDAY,
                    ],
                    [
                        'label' => $ll . 'configuration.day.' . Configuration::DAY_SUNDAY,
                        'value' => Configuration::DAY_SUNDAY,
                    ],
                    [
                        'label' => $ll . 'configuration.day.' . Configuration::DAY_SPECIAL_WEEKDAY,
                        'value' => Configuration::DAY_SPECIAL_WEEKDAY,
                    ],
                    [
                        'label' => $ll . 'configuration.day.' . Configuration::DAY_SPECIAL_BUSINESS,
                        'value' => Configuration::DAY_SPECIAL_BUSINESS,
                    ],
                    [
                        'label' => $ll . 'configuration.day.' . Configuration::DAY_SPECIAL_WORKDAY,
                        'value' => Configuration::DAY_SPECIAL_WORKDAY,
                    ],
                    [
                        'label' => $ll . 'configuration.day.' . Configuration::DAY_SPECIAL_WEEKEND,
                        'value' => Configuration::DAY_SPECIAL_WEEKEND,
                    ],
                ],
                'default' => Configuration::DAY_NONE,
                'maxitems' => 7,
            ],
            'displayCond' => [
                'OR' => [
                    'FIELD:frequency:=:' . Configuration::FREQUENCY_MONTHLY,
                    'FIELD:frequency:=:' . Configuration::FREQUENCY_YEARLY,
                ],
            ],
        ],
        'recurrence' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.recurrence',
            'exclude' => true,
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => $ll . 'configuration.recurrence.' . Configuration::RECURRENCE_NONE,
                        'value' => Configuration::RECURRENCE_NONE,
                    ],
                    [
                        'label' => $ll . 'configuration.recurrence.' . Configuration::RECURRENCE_FIRST,
                        'value' => Configuration::RECURRENCE_FIRST,
                    ],
                    [
                        'label' => $ll . 'configuration.recurrence.' . Configuration::RECURRENCE_SECOND,
                        'value' => Configuration::RECURRENCE_SECOND,
                    ],
                    [
                        'label' => $ll . 'configuration.recurrence.' . Configuration::RECURRENCE_THIRD,
                        'value' => Configuration::RECURRENCE_THIRD,
                    ],
                    [
                        'label' => $ll . 'configuration.recurrence.' . Configuration::RECURRENCE_FOURTH,
                        'value' => Configuration::RECURRENCE_FOURTH,
                    ],
                    [
                        'label' => $ll . 'configuration.recurrence.' . Configuration::RECURRENCE_FIFTH,
                        'value' => Configuration::RECURRENCE_FIFTH,
                    ],
                    [
                        'label' => $ll . 'configuration.recurrence.' . Configuration::RECURRENCE_LAST,
                        'value' => Configuration::RECURRENCE_LAST,
                    ],
                    [
                        'label' => $ll . 'configuration.recurrence.' . Configuration::RECURRENCE_NEXT_TO_LAST,
                        'value' => Configuration::RECURRENCE_NEXT_TO_LAST,
                    ],
                    [
                        'label' => $ll . 'configuration.recurrence.' . Configuration::RECURRENCE_THIRD_LAST,
                        'value' => Configuration::RECURRENCE_THIRD_LAST,
                    ],
                ],
                'default' => Configuration::RECURRENCE_NONE,
            ],
            'displayCond' => [
                'OR' => [
                    'FIELD:frequency:=:' . Configuration::FREQUENCY_MONTHLY,
                    'FIELD:frequency:=:' . Configuration::FREQUENCY_YEARLY,
                ],
            ],
        ],
        'end_date_dynamic' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.end_date_dynamic',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => '',
                        'value' => '',
                    ],
                    [
                        'label' => $ll . 'configuration.end_date_dynamic.' . Configuration::END_DYNAMIC_1_DAY,
                        'value' => Configuration::END_DYNAMIC_1_DAY,
                    ],
                    [
                        'label' => $ll . 'configuration.end_date_dynamic.' . Configuration::END_DYNAMIC_1_WEEK,
                        'value' => Configuration::END_DYNAMIC_1_WEEK,
                    ],
                    [
                        'label' => $ll . 'configuration.end_date_dynamic.' . Configuration::END_DYNAMIC_END_WEEK,
                        'value' => Configuration::END_DYNAMIC_END_WEEK,
                    ],
                    [
                        'label' => $ll . 'configuration.end_date_dynamic.' . Configuration::END_DYNAMIC_END_MONTH,
                        'value' => Configuration::END_DYNAMIC_END_MONTH,
                    ],
                    [
                        'label' => $ll . 'configuration.end_date_dynamic.' . Configuration::END_DYNAMIC_END_YEAR,
                        'value' => Configuration::END_DYNAMIC_END_YEAR,
                    ],
                ],
                'default' => '',
            ],
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
        ],
        'import_id' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.import_id',
            'exclude' => true,
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
    ],
    'palettes' => [
        'base' => [
            'label' => $ll . 'base_configuration',
            'showitem' => 'type,handling,state',
        ],
        'date' => [
            'label' => $ll . 'date.duration',
            'showitem' => 'start_date,end_date,end_date_dynamic',
        ],
        'time' => [
            'label' => $ll . 'time',
            'showitem' => 'start_time,end_time,open_end_time,--linebreak--,all_day',
        ],
        'termination_condition' => [
            'label' => $ll . 'termination_condition',
            'showitem' => 'till_date,till_days,till_days_relative,till_days_past,--linebreak--,counter_amount',
        ],
        'frequency_configuration' => [
            'label' => $ll . 'frequency_configuration',
            'showitem' => 'counter_interval,recurrence,day',
        ],
        'access' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access',
            'showitem' => 'starttime, endtime, --linebreak--, hidden',
        ],
        'language' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.language',
            'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource',
        ],
    ],
    'types' => [
        Configuration::TYPE_TIME => [
            'showitem' => '
                --palette--;;base,
                --palette--;;date,
                --palette--;;time,
                --div--;' . $ll . 'tx_calendarize_domain_model_configuration.frequency,
                frequency,
                --palette--;;termination_condition,
                --palette--;;frequency_configuration,import_id,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;access,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
            ',
        ],
        Configuration::TYPE_GROUP => [
            'showitem' => '
                --palette--;;base,groups,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
            ',
        ],
        Configuration::TYPE_EXTERNAL => [
            'showitem' => '
                --palette--;;base,external_ics_url,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
            ',
        ],
    ],
];
