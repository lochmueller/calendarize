<?php
/**
 * General ext_localconf file and also an example for your own extension
 *
 * @category   Extension
 * @package    Autoloader
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\HDNET\Autoloader\Loader::extLocalconf('HDNET', 'calendarize');

\HDNET\Calendarize\Register::extLocalconf(\HDNET\Calendarize\Register::getDefaultCalendarizeConfiguration());

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin('HDNET.' . $_EXTKEY, 'Calendar', array('Calendar' => 'list,year,month,week,day,detail'), array('Calendar' => 'list,year,month,week,day,detail'));