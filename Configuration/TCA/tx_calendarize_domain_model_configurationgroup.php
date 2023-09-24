<?php

declare(strict_types=1);

$ll = 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:';

return [
    'ctrl' => [
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'dividers2tabs' => '1',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
            'starttime' => 'starttime',
        ],
        'editlock' => 'editlock',
        'iconfile' => 'EXT:calendarize/Resources/Public/Icons/ConfigurationGroup.png',
        'label' => 'title',
        'origUid' => 't3_origuid',
        'searchFields' => 'uid,title',
        'sortby' => 'sorting',
        'title' =>  $ll . 'tx_calendarize_domain_model_configuration',
        'tstamp' => 'tstamp',
        'versioningWS' => 1,
    ],
    'palettes' => [
        'access' => [
            'showitem' => 'starttime, endtime, --linebreak--, hidden, editlock, --linebreak--, fe_group',
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                title,configurations,calendarize_info,import_id,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
            ',
        ],
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
        'title' => [
            'label' => $ll . 'tx_calendarize_domain_model_configurationgroup.title',
            'exclude' => false,
            'config' => [
                'type' => 'input',
                'eval' => 'trim,required',
            ],
        ],
        'configurations' => [
            'label' => 'Calendarize',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_calendarize_domain_model_configuration',
                'minitems' => 1,
                'maxitems' => 99,
                'behaviour' => [
                    'enableCascadingDelete' => true
                ]
            ],
        ],
        'calendarize_info' => [
            'label' => $ll . 'tca.information',
            'config' => [
                'type' => 'none',
                'renderType' => 'calendarizeInfoElement',
                'parameters' => [
                    'items' => 10,
                ],
            ],
        ],
    ],
];
