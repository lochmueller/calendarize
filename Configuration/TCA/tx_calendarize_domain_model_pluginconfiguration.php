<?php

use HDNET\Autoloader\Utility\ArrayUtility;
use HDNET\Autoloader\Utility\ModelUtility;

$base = ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\PluginConfiguration');

$defaultPidConfiguration = [
    'config' => [
        'type' => 'group',
        'internal_type' => 'db',
        'allowed' => 'pages',
        'size' => '1',
        'maxitems' => '1',
        'minitems' => '0',
        'show_thumbs' => '1',
        'wizards' => [
            'suggest' => [
                'type' => 'suggest'
            ]
        ],
    ],
];

$custom = [
    'columns' => [
        'model_name' => [
            'config' => [
                'type' => 'select',
                'items' => [
                    ['Default', \HDNET\Calendarize\Domain\Model\PluginConfiguration::class]
                ],
            ],
        ],
        'configuration' => [
            'config' => [
                'type' => 'select',
                'itemsProcFunc' => 'HDNET\Calendarize\Service\PluginConfiguration->addConfig',
                'renderMode' => 'checkbox',
                'minitems' => '1',
                'maxitems' => '99',
            ],
        ],
        'storage_pid' => [
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => '3',
                'maxitems' => '50',
                'minitems' => '0',
                'show_thumbs' => '1',
                'wizards' => [
                    'suggest' => [
                        'type' => 'suggest'
                    ]
                ],
            ],
        ],
        'recursive' => [
            'config' => [
                'type' => 'select',
                'renderMode' => 'selectSingle',
                'size' => 1,
                'items' => [
                    ['LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:inherit', ''],
                    ['LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:recursive.I.0', '0'],
                    ['LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:recursive.I.1', '1'],
                    ['LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:recursive.I.2', '2'],
                    ['LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:recursive.I.3', '3'],
                    ['LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:recursive.I.4', '4'],
                    ['LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:recursive.I.5', '250'],
                ],
            ],
        ],
        'detail_pid' => $defaultPidConfiguration,
        'list_pid' => $defaultPidConfiguration,
        'year_pid' => $defaultPidConfiguration,
        'month_pid' => $defaultPidConfiguration,
        'week_pid' => $defaultPidConfiguration,
        'day_pid' => $defaultPidConfiguration,
        'booking_pid' => $defaultPidConfiguration,
    ],
];

$tca = ArrayUtility::mergeRecursiveDistinct($base, $custom);

$search = [
    ',detail_pid'
];
$replace = [
    ',--div--;PID,detail_pid'
];

$tca['types']['1']['showitem'] = str_replace($search, $replace, $tca['types']['1']['showitem']);

return $tca;
