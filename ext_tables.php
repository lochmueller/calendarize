<?php
/**
 * General ext_tables file and also an example for your own extension
 *
 * @category   Extension
 * @package    Calendarize
 * @author     Tim Lochmüller
 */

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['calendarize']);

\HDNET\Autoloader\Loader::extTables('HDNET', 'calendarize', \HDNET\Calendarize\Register::getDefaultAutoloader());

if (!(boolean)$extensionConfiguration['disableDefaultEvent']) {
	\HDNET\Calendarize\Register::extTables(\HDNET\Calendarize\Register::getDefaultCalendarizeConfiguration());
}

$pluginName = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pluginName', 'calendarize');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('calendarize', 'Calendar', $pluginName);

// module icon
\TYPO3\CMS\Backend\Sprite\SpriteManager::addTcaTypeIcon('pages', 'contains-calendar', '../typo3conf/ext/calendarize/ext_icon.png');
$addCalendarizeToModuleSelection = TRUE;
foreach ($GLOBALS['TCA']['pages']['columns']['module']['config']['items'] as $item) {
	if ($item[1] === 'calendar') {
		$addCalendarizeToModuleSelection = FALSE;
		continue;
	}
}
if ($addCalendarizeToModuleSelection) {
	$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = array(
		0 => 'Calendarize',
		1 => 'calendar',
		2 => '../typo3conf/ext/calendarize/ext_icon.png'
	);
}