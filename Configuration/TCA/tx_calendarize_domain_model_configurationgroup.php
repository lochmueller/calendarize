<?php

declare(strict_types=1);

$ll = 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_calendarize_domain_model_configurationgroup',
        'label' => 'title',
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
        'iconfile' => 'EXT:calendarize/Resources/Public/Icons/ConfigurationGroup.png',
        'searchFields' => 'uid,title',
    ],
    'columns' => [
        'fe_group' => $GLOBALS['TCA']['tt_content']['columns']['fe_group'],
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
        'title' => [
            'label' => $ll . 'tx_calendarize_domain_model_configurationgroup.title',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'import_id' => [
            'label' => $ll . 'tx_calendarize_domain_model_configurationgroup.import_id',
            'exclude' => true,
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        // The columns 'configurations' and 'calendarize_info' are added in overrides
    ],
    'palettes' => [
        'access' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access',
            'showitem' => 'starttime, endtime, --linebreak--, hidden, editlock, --linebreak--, fe_group',
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                title,configurations,calendarize_info,import_id,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;access,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
            ',
        ],
    ],
];
