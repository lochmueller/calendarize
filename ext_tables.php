<?php
/**
 * General ext_tables file
 *
 * @category Extension
 * @package  Calendarize
 * @author   Tim Lochmüller
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\HDNET\Autoloader\Loader::extTables('HDNET', 'calendarize', \HDNET\Calendarize\Register::getDefaultAutoloader());

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_calendarize_domain_model_configuration,tx_calendarize_domain_model_index');

if (!(boolean)\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \HDNET\Calendarize\Register::extTables(\HDNET\Calendarize\Register::getDefaultCalendarizeConfiguration());
    \TYPO3\CMS\Core\Category\CategoryRegistry::getInstance()
        ->add('calendarize', 'tx_calendarize_domain_model_event');
}

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
    $GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',calendarize';
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'calendarize',
    'Calendar',
    \HDNET\Calendarize\Utility\TranslateUtility::get('pluginName')
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

\TYPO3\CMS\Core\Category\CategoryRegistry::getInstance()
    ->add('calendarize', 'tx_calendarize_domain_model_pluginconfiguration');

#\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
#    TCEMAIN.linkHandler {
#        event {
#            handler = HDNET\\Calendarize\\LinkHandling\\EventSelectionLinkHandler
#            label = Events
#        }
#    }
#');
