<?php

/**
 * TCA Structure for Index
 */

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Index');

$custom = [
    'ctrl' => [
        'hideTable' => true,
        'rootLevel' => -1,
        'label_alt' => 'start_date',
        'label_alt_force' => '1',
    ],
    'columns' => [
        'unique_register_key' => [
            'config' => [
                'readOnly' => '1',
            ]
        ],
        'foreign_uid' => [
            'config' => [
                'readOnly' => '1',
            ],
        ],
        'foreign_table' => [
            'config' => [
                'readOnly' => '1',
            ],
        ],
        'start_date' => [
            'config' => [
                'readOnly' => '1',
                'eval' => 'date',
            ],
        ],
        'end_date' => [
            'config' => [
                'readOnly' => '1',
                'eval' => 'date',
            ],
        ],
        'start_time' => [
            'config' => [
                'readOnly' => '1',
                'eval' => 'time',
            ],
        ],
        'end_time' => [
            'config' => [
                'readOnly' => '1',
                'eval' => 'time',
            ],
        ],
        'all_day' => [
            'config' => [
                'readOnly' => '1',
            ],
        ],
    ],
];

return \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);