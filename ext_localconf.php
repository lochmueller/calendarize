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

    // hooks
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['calendarize_calendar'] =
        \HDNET\Calendarize\Hooks\CmsLayout::class . '->calendarize_calendar';
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerIndexerConfiguration']['calendarize'] =
        \HDNET\Calendarize\Hooks\KeSearchIndexer::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer']['calendarize'] =
        \HDNET\Calendarize\Hooks\KeSearchIndexer::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['calendarize'] =
        \HDNET\Calendarize\Hooks\ProcessCmdmapClass::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['calendarize'] =
        \HDNET\Calendarize\Hooks\ProcessCmdmapClass::class;

    // Include new content elements to modWizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig("
        @import 'EXT:calendarize/Configuration/TsConfig/Page/Mod/Wizards/NewContentElement.tsconfig'
        @import 'EXT:calendarize/Configuration/TsConfig/Page/TCEMAIN/LinkHandler.tsconfig'
    ");

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1591803668] = [
        'nodeName' => 'calendarizeInfoElement',
        'priority' => 40,
        'class' => \HDNET\Calendarize\Form\Element\CalendarizeInfoElement::class,
    ];

    if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('workspaces')) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Workspaces\Controller\Remote\RemoteServer::class] = [
            'className' => \HDNET\Calendarize\Xclass\WorkspaceRemoteServer::class,
        ];
    }
})();
