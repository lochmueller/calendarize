<?php

$calendarizeIcons = [
    'ext-calendarize-wizard-icon' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:calendarize/Resources/Public/Icons/Extension.svg'
    ]
];

$bitmapIcons = [
    // module icon
    'apps-pagetree-folder-contains-calendarize' => 'apps-pagetree-folder-contains-calendarize.svg',
    // configuration types
    'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_TIME => 'Configuration.png',
    'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_GROUP => 'ConfigurationGroupType.png',
    'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_EXTERNAL => 'ConfigurationExternal.png',
];
foreach ($bitmapIcons as $identifier => $path) {
    $calendarizeIcons[$identifier] = [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        'source' => 'EXT:calendarize/Resources/Public/Icons/' . $path
    ];
}

return $calendarizeIcons;
