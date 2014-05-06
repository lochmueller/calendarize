<?php
/**
 * Local configuration
 *
 * @category   Extension
 * @package    Hdnet
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\HDNET\Autoloader\Loader::extLocalconf('HDNET', 'calendarize');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin('HDNET.Calendarize', 'Calendarize', array('Calendar' => 'year,month,week,list,detail'), array('Calendar' => 'year,month,week,list,detail'));

/**
 * Enable the calender function for the given tables
 * add this line to ext_localconf and ext_tables of your extension
 */
\HDNET\Calendarize\Utility\CalendarUtility::calendarize('tt_content');

