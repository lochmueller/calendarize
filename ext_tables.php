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
}

$pluginName = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pluginName', 'calendarize');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('calendarize', 'Calendar', $pluginName);

// module icon
$relIconPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('calendarize') . 'ext_icon.png';
\TYPO3\CMS\Backend\Sprite\SpriteManager::addTcaTypeIcon('pages', 'contains-calendar', $relIconPath);