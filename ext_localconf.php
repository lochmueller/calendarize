<?php

/**
 * General ext_localconf file.
 */

defined('TYPO3') or exit();

(function () {
    $GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize']['ConfigurationGroup'] = [
        'uniqueRegisterKey' => 'ConfigurationGroup',
        'title' => 'Calendarize Configuration Group',
        'modelName' => \HDNET\Calendarize\Domain\Model\ConfigurationGroup::class,
        'partialIdentifier' => 'ConfigurationGroup',
        'tableName' => 'tx_calendarize_domain_model_configurationgroup',
        'required' => true,
        'fieldName' => 'configurations',
    ];

    if (!(bool)\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize']['Event'] = [
            'uniqueRegisterKey' => 'Event',
            'title' => 'Calendarize Event',
            'modelName' => \HDNET\Calendarize\Domain\Model\Event::class,
            'partialIdentifier' => 'Event',
            'tableName' => 'tx_calendarize_domain_model_event',
            'required' => true,
        ];
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

    $GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['record'] = \HDNET\Calendarize\Typolink\DatabaseRecordLinkBuilder::class;

    // hooks
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

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('workspaces')) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Workspaces\Controller\Remote\RemoteServer::class] = [
            'className' => \HDNET\Calendarize\Xclass\WorkspaceRemoteServer::class,
        ];
    }
})();
