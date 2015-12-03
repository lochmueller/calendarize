<?php

/**
 * TCA Structure for Index
 */

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Index');

$custom = array(
    'ctrl' => array(
        'hideTable' => true,
        'rootLevel' => -1,
        'label_alt' => 'start_date',
        'label_alt_force' => '1',
    ),
    'columns' => array(
        'unique_register_key' => array(
            'config' => array(
                'readOnly' => '1',
            )
        ),
        'foreign_uid' => array(
            'config' => array(
                'readOnly' => '1',
            ),
        ),
        'foreign_table' => array(
            'config' => array(
                'readOnly' => '1',
            ),
        ),
        'start_date' => array(
            'config' => array(
                'readOnly' => '1',
                'eval' => 'date',
            ),
        ),
        'end_date' => array(
            'config' => array(
                'readOnly' => '1',
                'eval' => 'date',
            ),
        ),
        'start_time' => array(
            'config' => array(
                'readOnly' => '1',
                'eval' => 'time',
            ),
        ),
        'end_time' => array(
            'config' => array(
                'readOnly' => '1',
                'eval' => 'time',
            ),
        ),
        'all_day' => array(
            'config' => array(
                'readOnly' => '1',
            ),
        ),
    ),
);

return \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);