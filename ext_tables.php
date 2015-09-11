<?php
/**
 * General ext_tables file and also an example for your own extension
 *
 * @category Extension
 * @package  Calendarize
 * @author   Tim Lochmüller
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\HDNET\Autoloader\Loader::extTables('HDNET', 'calendarize', \HDNET\Calendarize\Register::getDefaultAutoloader());

if (!(boolean)\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \HDNET\Calendarize\Register::extTables(\HDNET\Calendarize\Register::getDefaultCalendarizeConfiguration());
    \TYPO3\CMS\Core\Category\CategoryRegistry::getInstance()
        ->add('calendarize', 'tx_calendarize_domain_model_event');
}

$pluginName = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pluginName', 'calendarize');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('calendarize', 'Calendar', $pluginName);
if (\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.0.0')) {
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['calendarize_calendar'] .= ',categories';
}

// module icon
$relIconPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('calendarize') . 'ext_icon.png';
\TYPO3\CMS\Backend\Sprite\SpriteManager::addTcaTypeIcon('pages', 'contains-calendar', $relIconPath);