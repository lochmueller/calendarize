<?php

use HDNET\Autoloader\Utility\ArrayUtility;
use HDNET\Autoloader\Utility\ModelUtility;
use HDNET\Calendarize\Domain\Model\Index;

$base = ModelUtility::getTcaInformation(Index::class);

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
            ],
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
                'eval' => 'datetime',
            ],
        ],
        'end_date' => [
            'config' => [
                'readOnly' => '1',
                'eval' => 'datetime',
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
        'state' => [
            'config' => [
                'readOnly' => '1',
            ],
        ],
    ],
];

return ArrayUtility::mergeRecursiveDistinct($base, $custom);
