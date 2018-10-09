<?php

declare(strict_types=1);
/**
 * General ext_localconf file.
 */
if (!\defined('TYPO3_MODE')) {
    die('Access denied.');
}

\HDNET\Autoloader\Loader::extLocalconf('HDNET', 'calendarize', \HDNET\Calendarize\Register::getDefaultAutoloader());

if (!(bool) \HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \HDNET\Calendarize\Register::extLocalconf(\HDNET\Calendarize\Register::getDefaultCalendarizeConfiguration());
    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $signalSlotDispatcher->connect(
        \HDNET\Calendarize\Command\ImportCommandController::class,
        'importCommand',
        \HDNET\Calendarize\Slots\EventImport::class,
        'importCommand'
    );

    $signalSlotDispatcher->connect(
        \HDNET\Calendarize\Controller\BookingController::class,
        'bookingAction',
        \HDNET\Calendarize\Slots\BookingCountries::class,
        'bookingSlot'
    );
    $signalSlotDispatcher->connect(
        \HDNET\Calendarize\Controller\BookingController::class,
        'sendAction',
        \HDNET\Calendarize\Slots\BookingCountries::class,
        'sendSlot'
    );
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'HDNET.calendarize',
    'Calendar',
    [
        'Calendar' => 'list,past,latest,year,month,week,day,detail,search,result,single',
        'Booking' => 'booking,send',
    ],
    [
        'Calendar' => 'search,result',
        'Booking' => 'booking,send',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\HDNET\Calendarize\Updates\CalMigrationUpdate::class] = \HDNET\Calendarize\Updates\CalMigrationUpdate::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\HDNET\Calendarize\Updates\NewIncludeExcludeStructureUpdate::class] = \HDNET\Calendarize\Updates\NewIncludeExcludeStructureUpdate::class;

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:calendarize/Configuration/TsConfig/ContentElementWizard.txt">');

$icons = [
    'ext-calendarize-wizard-icon' => 'ext_icon.svg',
];
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
foreach ($icons as $identifier => $path) {
    $iconRegistry->registerIcon(
        $identifier,
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:calendarize/' . $path]
    );
}

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('google_services')) {
    \FRUIT\GoogleServices\Service\SitemapProvider::addProvider(\HDNET\Calendarize\Service\SitemapProvider\Events::class);
}

// $GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler']['event'] = \HDNET\Calendarize\LinkHandling\EventLinkHandler::class;
