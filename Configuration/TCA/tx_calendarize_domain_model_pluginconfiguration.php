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
                'renderType' => 'selectSingle',
                'items' => [
                    ['Default', \HDNET\Calendarize\Domain\Model\PluginConfiguration::class]
                ],
            ],
        ],
        'configuration' => [
            'config' => [
                'type' => 'select',
                'itemsProcFunc' => 'HDNET\Calendarize\Service\PluginConfigurationService->addConfig',
                'renderType' => 'selectCheckBox',
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
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:inherit', ''],
                    ['LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:recursive.I.0', '0'],
                    ['LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:recursive.I.1', '1'],
                    ['LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:recursive.I.2', '2'],
                    ['LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:recursive.I.3', '3'],
                    ['LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:recursive.I.4', '4'],
                    ['LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:recursive.I.5', '250'],
                ],
                'size' => 1,
                'minitems' => '1',
                'maxitems' => '1',
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
    ',detail_pid',
    ',storage_pid,recursive' // @todo handle the persistence override in the controller
];
$replace = [
    ',--div--;PID,detail_pid',
    '',
];

$tca['types']['1']['showitem'] = str_replace($search, $replace, $tca['types']['1']['showitem']);


unset($tca['columns']['recursive']['config']['eval']);
return $tca;
