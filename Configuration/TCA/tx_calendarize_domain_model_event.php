<?php

declare(strict_types=1);

$ll = 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_calendarize_domain_model_event',
        'label' => 'title',
        'label_userFunc' => 'HDNET\\Calendarize\\Service\\TcaService->eventTitle',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
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
        'iconfile' => 'EXT:calendarize/Resources/Public/Icons/Event.png',
        'thumbnail' => 'images',
        'searchFields' => 'uid,title,description',
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
                'foreign_table' => 'tx_calendarize_domain_model_event',
                'foreign_table_where' => 'AND tx_calendarize_domain_model_event.pid=###CURRENT_PID###
                    AND tx_calendarize_domain_model_event.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'title' => [
            'label' => $ll . 'tx_calendarize_domain_model_event.title',
            'config' => [
                'type' => 'input',
                'required' => true,
            ],
            'l10n_mode' => 'prefixLangTitle',
        ],
        'slug' => [
            'label' => $ll . 'tx_calendarize_domain_model_event.slug',
            'config' => [
                'type' => 'slug',
                'prependSlash' => false,
                'generatorOptions' => [
                    'fields' => [
                        0 => 'title',
                    ],
                    'prefixParentPageSlug' => false,
                    'replacements' => [
                        '/' => '-',
                    ],
                ],
                'fallbackCharacter' => '-',
                'eval' => 'unique',
            ],
        ],
        'abstract' => [
            'label' => $ll . 'tx_calendarize_domain_model_event.abstract',
            'config' => [
                'type' => 'text',
            ],
        ],
        'description' => [
            'label' => $ll . 'tx_calendarize_domain_model_event.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => '1',
                'softref' => 'typolink_tag,email[subst],url',
            ],
        ],
        'location' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_event.location',
            'config' => [
                'type' => 'input',
            ],
        ],
        'location_link' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_event.location_link',
            'config' => [
                'type' => 'link',
            ],
        ],
        'organizer' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_event.organizer',
            'config' => [
                'type' => 'input',
            ],
        ],
        'organizer_link' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_event.organizer_link',
            'config' => [
                'type' => 'link',
            ],
        ],
        'images' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_event.images',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-image-types',
            ],
        ],
        'downloads' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_event.downloads',
            'config' => [
                'type' => 'file',
                'allowed' => '',
                'disallowed' => '',
            ],
        ],
        'import_id' => [
            'exclude' => true,
            'label' => $ll . 'tx_calendarize_domain_model_event.import_id',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
            'displayCond' => 'HIDE_FOR_NON_ADMINS',
        ],
        'categories' => [
            'config' => [
                'type' => 'category',
            ],
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
        ],
        't3_origuid' => [
            'config' => [
                'type' => 'passthrough',
                'default' => 0,
            ],
        ],
        // The columns 'calendarize' and 'calendarize_info' are added in overrides
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
        'location' => [
            'showitem' => 'location,location_link',
        ],
        'organizer' => [
            'showitem' => 'organizer,organizer_link',
        ],
    ],
    'types' => [
        1 => [
            'showitem' => '
                title,slug,abstract,description,--palette--;;location,--palette--;;organizer,import_id,
                --div--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:dateOptions,calendarize,calendarize_info,
                --div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,
                --div--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:files,images,downloads,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;access,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
            ',
        ],
    ],
];
