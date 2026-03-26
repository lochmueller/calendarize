<?php

/**
 * General ext_localconf file.
 */
defined('TYPO3') or exit();

use HDNET\Calendarize\Controller\BookingController;
use HDNET\Calendarize\Controller\CalendarController;
use HDNET\Calendarize\Form\Element\CalendarizeInfoElement;
use HDNET\Calendarize\Hooks\KeSearchIndexer;
use HDNET\Calendarize\Hooks\ProcessCmdmapClass;
use HDNET\Calendarize\Hooks\ProcessDatamapClass;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Typolink\DatabaseRecordLinkBuilder;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Workspaces\Controller\Remote\RemoteServer;

(function () {
    Register::extLocalconf(Register::getGroupCalendarizeConfiguration());
    if (!(bool)ConfigurationUtility::get('disableDefaultEvent')) {
        Register::extLocalconf(Register::getDefaultCalendarizeConfiguration());
    }

    $calendar = CalendarController::class;
    $booking = BookingController::class;

    ExtensionUtility::configurePlugin(
        'calendarize',
        'ListDetail',
        [
            $calendar => 'list,detail',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'List',
        [
            $calendar => 'list',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Detail',
        [
            $calendar => 'detail',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Search',
        [
            $calendar => 'search',
        ],
        [
            $calendar => 'search',
        ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Result',
        [
            $calendar => 'result',
        ],
        [
            $calendar => 'result',
        ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Latest',
        [
            $calendar => 'latest',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Single',
        [
            $calendar => 'single',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Year',
        [
            $calendar => 'year',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Quarter',
        [
            $calendar => 'quarter',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Month',
        [
            $calendar => 'month',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Week',
        [
            $calendar => 'week',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Day',
        [
            $calendar => 'day',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Past',
        [
            $calendar => 'past',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );
    ExtensionUtility::configurePlugin(
        'calendarize',
        'Shortcut',
        [
            $calendar => 'shortcut',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Calendar',
        [
            $calendar => 'list,past,latest,year,quater,month,week,day,detail,search,result,single,shortcut',
            $booking => 'booking,send',
        ],
        [
            $calendar => 'search,result',
            $booking => 'booking,send',
        ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'calendarize',
        'Booking',
        [
            $booking => 'booking,send',
        ],
        [
            $booking => 'booking,send',
        ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    $GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['record'] = DatabaseRecordLinkBuilder::class;

    // hooks
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerIndexerConfiguration']['calendarize'] =
        KeSearchIndexer::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer']['calendarize'] =
        KeSearchIndexer::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['calendarize'] =
        ProcessCmdmapClass::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['calendarize'] =
        ProcessDatamapClass::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1591803668] = [
        'nodeName' => 'calendarizeInfoElement',
        'priority' => 40,
        'class' => CalendarizeInfoElement::class,
    ];

    if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() <= 12) {
        // Include new content elements to modWizards
        ExtensionManagementUtility::addPageTSConfig('
            @import \'EXT:calendarize/Configuration/page.tsconfig\'
        ');
    }
})();
