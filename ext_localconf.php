<?php

/**
 * General ext_localconf file.
 */

defined('TYPO3') or exit();

(function () {
    \HDNET\Autoloader\Loader::extLocalconf('HDNET', 'calendarize', \HDNET\Calendarize\Register::getDefaultAutoloader());
    \HDNET\Calendarize\Register::extLocalconf(\HDNET\Calendarize\Register::getGroupCalendarizeConfiguration());

    if (!(bool)\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
        \HDNET\Calendarize\Register::extLocalconf(\HDNET\Calendarize\Register::getDefaultCalendarizeConfiguration());
    }

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'calendarize',
        'Calendar',
        [
            \HDNET\Calendarize\Controller\CalendarController::class => 'list,past,latest,year,quater,month,week,day,detail,search,result,single,shortcut',
            \HDNET\Calendarize\Controller\BookingController::class => 'booking,send',
        ],
        [
            \HDNET\Calendarize\Controller\CalendarController::class => 'search,result',
            \HDNET\Calendarize\Controller\BookingController::class => 'booking,send',
        ]
    );

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['calendarize_calMigration'] = \HDNET\Calendarize\Updates\CalMigrationUpdate::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['calendarize_newIncludeExcludeStructure'] = \HDNET\Calendarize\Updates\NewIncludeExcludeStructureUpdate::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['calendarize_dateField'] = \HDNET\Calendarize\Updates\DateFieldUpdate::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['calendarize_tillDateField'] = \HDNET\Calendarize\Updates\TillDateFieldUpdate::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['calendarize_populateEventSlugs'] = \HDNET\Calendarize\Updates\PopulateEventSlugs::class;

    $GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['record'] = \HDNET\Calendarize\Typolink\DatabaseRecordLinkBuilder::class;

    // Include new content elements to modWizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig("
        @import 'EXT:calendarize/Configuration/TsConfig/Page/Mod/Wizards/NewContentElement.tsconfig'
        @import 'EXT:calendarize/Configuration/TsConfig/Page/TCEMAIN/LinkHandler.tsconfig'
    ");

    $svgIcons = [
        'ext-calendarize-wizard-icon' => 'Extension.svg',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($svgIcons as $identifier => $path) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:calendarize/Resources/Public/Icons/' . $path]
        );
    }

    $bitmapIcons = [
        // module icon
        'apps-pagetree-folder-contains-calendarize' => 'apps-pagetree-folder-contains-calendarize.svg',
        // configuration types
        'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_TIME => 'Configuration.png',
        'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_GROUP => 'ConfigurationGroupType.png',
        'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_EXTERNAL => 'ConfigurationExternal.png',
    ];
    foreach ($bitmapIcons as $identifier => $path) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            ['source' => 'EXT:calendarize/Resources/Public/Icons/' . $path]
        );
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1591803668] = [
        'nodeName' => 'calendarizeInfoElement',
        'priority' => 40,
        'class' => \HDNET\Calendarize\Form\Element\CalendarizeInfoElement::class,
    ];

    if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('workspaces')) {
        \HDNET\Autoloader\Utility\ExtendedUtility::addXclass(\TYPO3\CMS\Workspaces\Controller\Remote\RemoteServer::class, \HDNET\Calendarize\Xclass\WorkspaceRemoteServer::class);
    }
})();
