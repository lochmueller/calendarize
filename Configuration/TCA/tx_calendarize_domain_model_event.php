<?php

/**
 * TCA Structure for Events
 */

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Event');

$custom = array(
    'ctrl' => array(
        'hideTable' => (boolean)\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent'),
        'searchFields' => 'uid,title,description',
        'thumbnail' => 'images',
    ),
    'columns' => array(
        'title' => array(
            'type' => 'text',
            'eval' => 'required'
        ),
        'abstract' => array(
            'config' => ['type' => 'text'],
        ),
        'import_id' => array(
            'readOnly' => true,
        ),
    ),
);

$tca = \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);

$search = [
    'images,downloads,',
    'language,--div--',
    'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended'
];
$replace = [
    ',',
    'language,--div--;' . \HDNET\Autoloader\Utility\TranslateUtility::getLllOrHelpMessage('files',
        'calendarize') . ',images,downloads,--div--',
    \HDNET\Autoloader\Utility\TranslateUtility::getLllOrHelpMessage('dateOptions', 'calendarize')
];

$tca['types']['1']['showitem'] = str_replace($search, $replace, $tca['types']['1']['showitem']);
return $tca;