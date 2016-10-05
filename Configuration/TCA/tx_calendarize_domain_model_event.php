<?php

use HDNET\Autoloader\Utility\ArrayUtility;
use HDNET\Autoloader\Utility\ModelUtility;
use HDNET\Autoloader\Utility\TranslateUtility;
use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Utility\ConfigurationUtility;

$base = ModelUtility::getTcaInformation(Event::class);

$custom = [
    'ctrl'    => [
        'hideTable'    => (boolean)ConfigurationUtility::get('disableDefaultEvent'),
        'searchFields' => 'uid,title,description',
        'thumbnail'    => 'images',
    ],
    'columns' => [
        'title'     => [
            'config' => [
                'eval' => 'required'
            ],
        ],
        'abstract'  => [
            'config' => [
                'type' => 'text'
            ],
        ],
        'import_id' => [
            'config' => [
                'readOnly' => true,
            ],
        ],
    ],
];

$tca = ArrayUtility::mergeRecursiveDistinct($base, $custom);

$search = [
    'images,downloads,',
    'language,--div--',
    'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended'
];
$replace = [
    ',',
    'language,--div--;' . TranslateUtility::getLllOrHelpMessage('files', 'calendarize') . ',images,downloads,--div--',
    TranslateUtility::getLllOrHelpMessage('dateOptions', 'calendarize')
];

$tca['types']['1']['showitem'] = str_replace($search, $replace, $tca['types']['1']['showitem']);
return $tca;
