<?php

/**
 * General ext_tables file.
 */
if (!\defined('TYPO3_MODE')) {
    die('Access denied.');
}

\HDNET\Autoloader\Loader::extTables('HDNET', 'calendarize', \HDNET\Calendarize\Register::getDefaultAutoloader());

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_calendarize_domain_model_configuration,tx_calendarize_domain_model_index');

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
    $GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',calendarize';
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'calendarize',
    'Calendar',
    \HDNET\Calendarize\Utility\TranslateUtility::getLll('pluginName')
);

// module icon
$extensionIcon = \HDNET\Autoloader\Utility\IconUtility::getByExtensionKey('calendarize', true);
/** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
    'apps-pagetree-folder-contains-calendarize',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => $extensionIcon]
);

$categoryRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Category\CategoryRegistry::class);
$categoryRegistry->add('calendarize', 'tx_calendarize_domain_model_pluginconfiguration');

//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
//    TCEMAIN.linkHandler {
//        event {
//            handler = HDNET\\Calendarize\\LinkHandling\\EventSelectionLinkHandler
//            label = Events
//        }
//    }
//');

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'HDNET.calendarize',
        'web',
        'calendarize',
        '',
        ['Backend' => 'list,option'],
        [
            // Additional configuration
            'access' => 'user, group',
            'icon' => 'EXT:calendarize/Resources/Public/Icons/Extension.svg',
            'iconIdentifier' => 'module-my_redirects',
            'labels' => 'LLL:EXT:calendarize/Resources/Private/Language/locallang_mod.xlf',
            'navigationComponentId' => ''
        ]
    );



$iconPath = \TYPO3\CMS\Core\Utility\PathUtility::getAbsoluteWebPath(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:calendarize/Resources/Public/Icons/'));
$iconRegistry->registerIcon(
    'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_TIME,
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => $iconPath . 'Configuration.png']
);
$iconRegistry->registerIcon(
    'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_GROUP,
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => $iconPath . 'ConfigurationGroupType.png']
);
$iconRegistry->registerIcon(
    'apps-calendarize-type-' . \HDNET\Calendarize\Domain\Model\Configuration::TYPE_EXTERNAL,
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => $iconPath . 'ConfigurationExternal.png']
);

// Exclude "pages" and obsolete fields
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['calendarize_calendar'] = 'recursive,select_key,pages';
