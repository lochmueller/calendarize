<?php
/**
 * General ext_localconf file and also an example for your own extension
 *
 * @category   Extension
 * @package    Calendarize
 * @author     Tim Lochmüller
 */

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['calendarize']);

\HDNET\Autoloader\Loader::extLocalconf('HDNET', 'calendarize', \HDNET\Calendarize\Register::getDefaultAutoloader());

if (!(boolean)$extensionConfiguration['disableDefaultEvent']) {
	\HDNET\Calendarize\Register::extLocalconf(\HDNET\Calendarize\Register::getDefaultCalendarizeConfiguration());
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin('HDNET.calendarize', 'Calendar', array('Calendar' => 'list,year,month,week,day,detail,search'), array('Calendar' => 'list,year,month,week,day,detail,search'));