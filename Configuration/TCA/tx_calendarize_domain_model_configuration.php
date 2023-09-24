<?php

declare(strict_types=1);

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Service\TcaService;
use HDNET\Calendarize\Utility\TranslateUtility;

$ll = 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:';

return [
    'ctrl' => [
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'dividers2tabs' => '1',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
            'starttime' => 'starttime',
        ],
        'editlock' => 'editlock',
        'formattedLabel_userFunc' => TcaService::class . '->configurationTitle',
        'hideTable' => true,
        'iconfile' => 'EXT:calendarize/Resources/Public/Icons/Configuration.png',
        'label' => 'type',
        'languageField' => 'sys_language_uid',
        'origUid' => 't3_origuid',
        'searchFields' => 'type,handling,state,start_date,end_date,end_date_dynamic,start_time,end_time,all_day,
            open_end_time,external_ics_url,groups,frequency,till_date,till_days,till_days_relative,till_days_past,
            counter_amount,counter_interval,recurrence,day,import_id',
        'sortby' => 'sorting',
        'title' =>  $ll . 'tx_calendarize_domain_model_configuration',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'transOrigPointerField' => 'l10n_parent',
        'tstamp' => 'tstamp',
        'type' => 'type',
        'typeicon_classes' => [
            Configuration::TYPE_EXTERNAL => 'apps-calendarize-type-' . Configuration::TYPE_EXTERNAL,
            Configuration::TYPE_GROUP => 'apps-calendarize-type-' . Configuration::TYPE_GROUP,
            Configuration::TYPE_TIME => 'apps-calendarize-type-' . Configuration::TYPE_TIME,
        ],
        'typeicon_column' => 'type',
        'versioningWS' => 1,
        'security' => [
            'ignorePageTypeRestriction' => true
        ]
    ],
    'columns' => [
        'fe_group' => $GLOBALS['TCA']['tt_content']['columns']['fe_group'],
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
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'type' => [
            'label' => $ll. 'tx_calendarize_domain_model_configuration.type',
            'exclude' => false,
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'authMode' => 'explicitDeny',
                'authMode_enforce' => 'strict',
                'items' => [
                    [$ll . 'configuration.type.time', 'time'],
                    [$ll . 'configuration.type.group', 'group'],
                    [$ll . 'configuration.type.external', 'external'],
                ],
                'default' => Configuration::TYPE_TIME,
            ],
        ],
        'handling' => [
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        TranslateUtility::getLll('configuration.handling.' . Configuration::HANDLING_INCLUDE),
                        Configuration::HANDLING_INCLUDE,
                    ],
                    [
                        TranslateUtility::getLll('configuration.handling.' . Configuration::HANDLING_EXCLUDE),
                        Configuration::HANDLING_EXCLUDE,
                    ],
                    [
                        TranslateUtility::getLll('configuration.handling.' . Configuration::HANDLING_OVERRIDE),
                        Configuration::HANDLING_OVERRIDE,
                    ],
                    [
                        TranslateUtility::getLll('configuration.handling.' . Configuration::HANDLING_CUTOUT),
                        Configuration::HANDLING_CUTOUT,
                    ],
                ],
                'default' => Configuration::HANDLING_INCLUDE,
            ],
        ],
        'state' => [
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        '',
                        Configuration::STATE_DEFAULT,
                    ],
                    [
                        $ll . 'configuration.state.canceled',
                        Configuration::STATE_CANCELED,
                    ],
                ],
                'default' => Configuration::STATE_DEFAULT,
            ],
        ],
        'start_date' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.start_date',
            'exclude' => false,
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'required,date',
                'dbType' => 'date',
                'size' => 13,
            ],
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
        ],
        'end_date' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.end_date',
            'exclude' => false,
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'date',
                'dbType' => 'date',
                'size' => 13,
            ],
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
        ],
        'start_time' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.start_time',
            'exclude' => false,
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'time,required',
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
            'exclude' => false,
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'time',
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
            'exclude' => false,
            'onChange' => 'reload',
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'open_end_time' => [
            'label' => $ll . 'tx_calendarize_domain_model_configuration.open_end_time',
            'exclude' => false,
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
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_NONE),
                        Configuration::FREQUENCY_NONE,
                    ],
                    [
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_MINUTELY),
                        Configuration::FREQUENCY_MINUTELY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_HOURLY),
                        Configuration::FREQUENCY_HOURLY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_DAILY),
                        Configuration::FREQUENCY_DAILY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_WEEKLY),
                        Configuration::FREQUENCY_WEEKLY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_MONTHLY),
                        Configuration::FREQUENCY_MONTHLY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_YEARLY),
                        Configuration::FREQUENCY_YEARLY,
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
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'date',
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
                'type' => 'input',
                'eval' => 'int,null',
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
                'type' => 'input',
                'eval' => 'int,null',
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
                'type' => 'input',
                'eval' => 'int',
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
                'type' => 'input',
                'eval' => 'int,required',
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
                'type' => 'input',
                'eval' => 'trim,required',
                'renderType' => 'inputLink',
                'softref' => 'typolink',
                'fieldControl' => [
                    'linkPopup' => [
                        'options' => [
                            'allowedExtensions' => 'ics',
                            'blindLinkOptions' => 'folder,mail,page,spec,telephone,tx_calendarize_domain_model_event',
                            'blindLinkFields' => 'class,target,title',
                        ],
                    ],
                ],
                'max' => 2048,
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
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_MONDAY),
                        Configuration::DAY_MONDAY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_TUESDAY),
                        Configuration::DAY_TUESDAY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_WEDNESDAY),
                        Configuration::DAY_WEDNESDAY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_THURSDAY),
                        Configuration::DAY_THURSDAY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_FRIDAY),
                        Configuration::DAY_FRIDAY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SATURDAY),
                        Configuration::DAY_SATURDAY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SUNDAY),
                        Configuration::DAY_SUNDAY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SPECIAL_WEEKDAY),
                        Configuration::DAY_SPECIAL_WEEKDAY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SPECIAL_BUSINESS),
                        Configuration::DAY_SPECIAL_BUSINESS,
                    ],
                    [
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SPECIAL_WORKDAY),
                        Configuration::DAY_SPECIAL_WORKDAY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SPECIAL_WEEKEND),
                        Configuration::DAY_SPECIAL_WEEKEND,
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
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_NONE),
                        Configuration::RECURRENCE_NONE,
                    ],
                    [
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_FIRST),
                        Configuration::RECURRENCE_FIRST,
                    ],
                    [
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_SECOND),
                        Configuration::RECURRENCE_SECOND,
                    ],
                    [
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_THIRD),
                        Configuration::RECURRENCE_THIRD,
                    ],
                    [
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_FOURTH),
                        Configuration::RECURRENCE_FOURTH,
                    ],
                    [
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_FIFTH),
                        Configuration::RECURRENCE_FIFTH,
                    ],
                    [
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_LAST),
                        Configuration::RECURRENCE_LAST,
                    ],
                    [
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_NEXT_TO_LAST),
                        Configuration::RECURRENCE_NEXT_TO_LAST,
                    ],
                    [
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_THIRD_LAST),
                        Configuration::RECURRENCE_THIRD_LAST,
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
            'exclude' => true,
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        '',
                        '',
                    ],
                    [
                        TranslateUtility::getLll('configuration.end_date_dynamic.' . Configuration::END_DYNAMIC_1_DAY),
                        Configuration::END_DYNAMIC_1_DAY,
                    ],
                    [
                        TranslateUtility::getLll('configuration.end_date_dynamic.' . Configuration::END_DYNAMIC_1_WEEK),
                        Configuration::END_DYNAMIC_1_WEEK,
                    ],
                    [
                        TranslateUtility::getLll('configuration.end_date_dynamic.' . Configuration::END_DYNAMIC_END_WEEK),
                        Configuration::END_DYNAMIC_END_WEEK,
                    ],
                    [
                        TranslateUtility::getLll('configuration.end_date_dynamic.' . Configuration::END_DYNAMIC_END_MONTH),
                        Configuration::END_DYNAMIC_END_MONTH,
                    ],
                    [
                        TranslateUtility::getLll('configuration.end_date_dynamic.' . Configuration::END_DYNAMIC_END_YEAR),
                        Configuration::END_DYNAMIC_END_YEAR,
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
            'label' =>  $ll . 'base_configuration',
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
            'showitem' => 'starttime, endtime, --linebreak--, hidden',
        ],
        'language' => [
            'showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource',
        ],
    ],
    'types' => [
        Configuration::TYPE_TIME => [
            'showitem' => '
                --palette--;;base,
                --palette--;;date,
                --palette--;;time,
                --div--;' . $ll . 'tx_calendarize_domain_model_configuration.frequency,frequency,
                --palette--;;termination_condition,
                --palette--;;frequency_configuration,import_id,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access,
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
