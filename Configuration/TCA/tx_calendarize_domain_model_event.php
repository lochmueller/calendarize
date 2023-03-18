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
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'title' => $ll . 'tx_calendarize_domain_model_event',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'dividers2tabs' => true,
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'searchFields' => 'uid,title,description',
        'iconfile' => 'EXT:calendarize/Resources/Public/Icons/Event.png',
        'thumbnail' => 'images',
        'label_userFunc' => 'HDNET\\Calendarize\\Service\\TcaService->eventTitle',
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
            'exclude' => false,
            'label' => $ll . 'tx_calendarize_domain_model_event.title',
            'config' => [
                'type' => 'input',
                'required' => true,
            ],
            'l10n_mode' => 'prefixLangTitle',
        ],
        'slug' => [
            'exclude' => false,
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
            'exclude' => false,
            'label' => $ll . 'tx_calendarize_domain_model_event.abstract',
            'config' => [
                'type' => 'text',
            ],
        ],
        'description' => [
            'exclude' => false,
            'label' => $ll . 'tx_calendarize_domain_model_event.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => '1',
                'richtextConfiguration' => 'default',
                'softref' => 'typolink_tag,email[subst],url',
            ],
            'defaultExtras' => 'richtext:rte_transform[flag=rte_enabled|mode=ts_css]',
        ],
        'location' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_event.location',
            'config' => [
                'type' => 'input',
            ],
        ],
        'location_link' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_event.location_link',
            'config' => [
                'type' => 'link',
            ],
        ],
        'organizer' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_event.organizer',
            'config' => [
                'type' => 'input',
            ],
        ],
        'organizer_link' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_event.organizer_link',
            'config' => [
                'type' => 'link',
            ],
        ],
        'images' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_event.images',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-image-types',
            ],
        ],
        'downloads' => [
            'exclude' => 1,
            'label' => $ll . 'tx_calendarize_domain_model_event.downloads',
            'config' => [
                'type' => 'file',
                'allowed' => '',
                'disallowed' => '',
                'foreign_table' => 'sys_file_reference',
                'foreign_field' => 'uid_foreign',
                'foreign_sortby' => 'sorting_foreign',
                'foreign_table_field' => 'tablenames',
                'foreign_match_fields' => [
                    'fieldname' => 'downloads',
                ],
                'foreign_label' => 'uid_local',
                'foreign_selector' => 'uid_local',
            ],
        ],
        'import_id' => [
            'exclude' => 1,
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
                'foreign_table' => 'sys_category',
                'size' => 20,
                'foreign_table_where' => ' AND {#sys_category}.{#sys_language_uid} IN (-1, 0)',
                'relationship' => 'manyToMany',
                'maxitems' => 99999,
                'default' => 0,
                'MM' => 'sys_category_record_mm',
                'MM_opposite_field' => 'items',
                'MM_match_fields' => [
                    'tablenames' => 'tx_calendarize_domain_model_event',
                    'fieldname' => 'categories',
                ],
            ],
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
            'exclude' => true,
        ],
        'calendarize' => [
            'label' => 'Calendarize',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_calendarize_domain_model_configuration',
                'minitems' => 1,
                'maxitems' => 99,
                'behaviour' => [
                    'enableCascadingDelete' => true,
                ],
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
                title,slug,abstract,description,location,location_link,organizer,organizer_link,import_id,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
                --div--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:files,images,downloads,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access,
                --div--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:dateOptions, calendarize, calendarize_info
            ',
        ],
    ],
];
