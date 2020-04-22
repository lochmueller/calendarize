<?php

declare(strict_types=1);

use HDNET\Autoloader\Utility\ArrayUtility;
use HDNET\Autoloader\Utility\ModelUtility;
use HDNET\Autoloader\Utility\TranslateUtility;
use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Service\TcaService;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Resource\File;

$base = ModelUtility::getTcaInformation(Event::class);

$custom = [
    'ctrl' => [
        'hideTable' => (bool)ConfigurationUtility::get('disableDefaultEvent'),
        'searchFields' => 'uid,title,description',
        'thumbnail' => 'images',
        'label_userFunc' => TcaService::class . '->eventTitle',
    ],
    'columns' => [
        'title' => [
            'config' => [
                'eval' => 'required',
            ],
        ],
        'abstract' => [
            'config' => [
                'type' => 'text',
            ],
        ],
        'import_id' => [
            'config' => [
                'readOnly' => true,
            ],
        ],
        'location_link' => [
            'config' => [
                'renderType' => 'inputLink',
            ],
        ],
        'organizer_link' => [
            'config' => [
                'renderType' => 'inputLink',
            ],
        ],
        'images' => [
            'config' => [
                // Use the imageoverlayPalette instead of the basicoverlayPalette
                'overrideChildTca' => [
                    'types' => [
                        '0' => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette',
                        ],
                        File::FILETYPE_TEXT => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette',
                        ],
                        File::FILETYPE_IMAGE => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette',
                        ],
                        File::FILETYPE_AUDIO => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.audioOverlayPalette;audioOverlayPalette,
                                --palette--;;filePalette',
                        ],
                        File::FILETYPE_VIDEO => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.videoOverlayPalette;videoOverlayPalette,
                                --palette--;;filePalette',
                        ],
                        File::FILETYPE_APPLICATION => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette',
                        ],
                    ],
                ],
            ],
        ],
    ],
];

$tca = ArrayUtility::mergeRecursiveDistinct($base, $custom);

$search = [
    'images,downloads,',
    'language,--div--',
    'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended',
];
$replace = [
    ',',
    'language,--div--;' . TranslateUtility::getLllOrHelpMessage('files', 'calendarize') . ',images,downloads,--div--',
    TranslateUtility::getLllOrHelpMessage('dateOptions', 'calendarize'),
];

$tca['types']['1']['showitem'] = \str_replace($search, $replace, $tca['types']['1']['showitem']);

return $tca;
