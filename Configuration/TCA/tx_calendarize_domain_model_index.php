<?php

declare(strict_types=1);

use HDNET\Autoloader\Utility\ArrayUtility;
use HDNET\Autoloader\Utility\ModelUtility;
use HDNET\Calendarize\Domain\Model\Index;

$base = ModelUtility::getTcaInformation(Index::class);

// Removed unused fields, because the records are not accessible by the user
unset(
    // Stores changes after translation was made
    $base['ctrl']['transOrigDiffSourceField'],
    $base['columns']['l10n_diffsource'],
    // Prevents editing of records for non admins
    $base['ctrl']['editlock'],
    $base['columns']['editlock'],
);

$custom = [
    'ctrl' => [
        'hideTable' => true,
        'rootLevel' => -1,
        'label_alt' => 'start_date',
        'label_alt_force' => '1',
        'readOnly' => true,
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
                'dbType' => 'date',
                'eval' => 'datetime',
                'format' => 'date',
            ],
        ],
        'end_date' => [
            'config' => [
                'readOnly' => '1',
                'dbType' => 'date',
                'eval' => 'datetime',
                'format' => 'date',
            ],
        ],
        'start_time' => [
            'config' => [
                'readOnly' => '1',
                'renderType' => 'inputDateTime',
                'eval' => 'time',
            ],
        ],
        'end_time' => [
            'config' => [
                'readOnly' => '1',
                'renderType' => 'inputDateTime',
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
