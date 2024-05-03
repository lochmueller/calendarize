<?php

$wizards = [
    'listdetail',
    'list',
    'detail',
    'latest',
    'search',
    'result',
    'single',
    'past',
    'booking',
    'day',
    'week',
    'month',
    'quarter',
    'year',
];

$calendarizeIcons = [
    'ext-calendarize-wizard-icon' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:calendarize/Resources/Public/Icons/Extension.svg',
    ],
    'apps-pagetree-folder-contains-calendarize' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:calendarize/Resources/Public/Icons/apps-pagetree-folder-contains-calendarize.svg',
    ],
];

foreach ($wizards as $wizard) {
    $calendarizeIcons['ext-calendarize-wizard-icon-' . $wizard] = [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:calendarize/Resources/Public/Icons/Wizard/' . ucfirst($wizard) . '.svg',
    ];
}

$bitmapIcons = [
    // configuration types
    'calendarize-cal-dav' => 'CalDav.png',
    'calendarize-configuration' => 'Configuration.png',
    'calendarize-configuration-external' => 'ConfigurationExternal.png',
    'calendarize-configuration-group' => 'ConfigurationGroup.png',
    'calendarize-configuration-group-Type' => 'ConfigurationGroupType.png',
    'calendarize-event' => 'Event.png',
    'calendarize-index' => 'Index.png',
    'calendarize-plugin-configuration' => 'PluginConfiguration.png',
    'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_TIME => 'Configuration.png',
    'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_GROUP => 'ConfigurationGroupType.png',
    'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_EXTERNAL => 'ConfigurationExternal.png',
];
foreach ($bitmapIcons as $identifier => $path) {
    $calendarizeIcons[$identifier] = [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        'source' => 'EXT:calendarize/Resources/Public/Icons/' . $path,
    ];
}

return $calendarizeIcons;
