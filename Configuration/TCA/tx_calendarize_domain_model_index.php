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
    $base['columns']['editlock']
);

$custom = [
    'ctrl' => [
        'hideTable' => true,
        'rootLevel' => -1,
        'label_alt' => 'start_date',
        'label_alt_force' => true,
        'readOnly' => true,
    ],
    'columns' => [
        'unique_register_key' => [
            'config' => [
                'readOnly' => true,
            ],
        ],
        'foreign_uid' => [
            'config' => [
                'readOnly' => true,
            ],
        ],
        'foreign_table' => [
            'config' => [
                'readOnly' => true,
            ],
        ],
        'start_date' => [
            'config' => [
                'readOnly' => true,
                'dbType' => 'date',
                'eval' => 'date',
            ],
        ],
        'end_date' => [
            'config' => [
                'readOnly' => true,
                'dbType' => 'date',
                'eval' => 'date',
            ],
        ],
        'start_time' => [
            'config' => [
                'readOnly' => true,
                'renderType' => 'inputDateTime',
                'eval' => 'time',
            ],
        ],
        'end_time' => [
            'config' => [
                'readOnly' => true,
                'renderType' => 'inputDateTime',
                'eval' => 'time',
            ],
        ],
        'all_day' => [
            'config' => [
                'readOnly' => true,
            ],
        ],
        'state' => [
            'config' => [
                'readOnly' => true,
            ],
        ],
        'slug' => [
            'config' => [
                'eval' => 'unique',
                'prependSlash' => false,
            ],
        ],
    ],
];

return ArrayUtility::mergeRecursiveDistinct($base, $custom);
