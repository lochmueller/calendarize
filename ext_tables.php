<?php
/**
 * Table configuration
 *
 * @category   Extension
 * @package    Hdnet
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\HDNET\Autoloader\Loader::extTables('HDNET', 'calendarize');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('calendarize', 'Calendarize', 'Calendarize');

/**
 * Enable the calender function for the given tables
 * add this line to ext_localconf and ext_tables of your extension
 */
\HDNET\Calendarize\Utility\CalendarUtility::calendarize('tt_content');