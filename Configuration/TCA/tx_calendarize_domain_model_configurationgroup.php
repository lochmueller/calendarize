<?php

/**
 * TCA Structure for ConfigurationGroups
 */

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\ConfigurationGroup');

$custom = [
    'ctrl' => [
        'searchFields' => 'uid,title',
    ],
    'types' => [
        '1' => [
            'showitem' => str_replace(
                'configurations,',
                'configurations,calendarize_info,',
                $base['types']['1']['showitem']
            )
        ],
    ],
    'columns' => [
        'configurations' => [
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_calendarize_domain_model_configuration',
                'minitems' => 1,
                'maxitems' => 100,
            ]
        ],
        'calendarize_info' => [
            'label' => 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:tca.information',
            'config' => [
                'type' => 'user',
                'userFunc' => 'HDNET\\Calendarize\\UserFunction\\TcaInformation->informationGroupField',
            ],
        ],
    ],
];

return \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);
