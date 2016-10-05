<?php

use HDNET\Autoloader\Utility\ArrayUtility;
use HDNET\Autoloader\Utility\ModelUtility;
use HDNET\Calendarize\Domain\Model\CalDav;

$base = ModelUtility::getTcaInformation(CalDav::class);

$custom = [
    'ctrl'    => [
        'rootLevel' => 1,
    ],
    'columns' => [
        'title'         => [
            'config' => [
                'eval' => 'required,alphanum,lower,trim,unique'
            ],
        ],
        'user_storage'  => [
            'config' => [
                'type'          => 'group',
                'internal_type' => 'db',
                'allowed'       => 'pages',
                'minitems'      => 1,
                'maxitems'      => 100,
            ],
        ],
        'event_storage' => [
            'config' => [
                'type'          => 'group',
                'internal_type' => 'db',
                'allowed'       => 'pages',
                'size'          => 1,
                'minitems'      => 1,
                'maxitems'      => 1,
            ],
        ],
    ],
];

return ArrayUtility::mergeRecursiveDistinct($base, $custom);
