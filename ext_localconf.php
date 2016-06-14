<?php
/**
 * General ext_localconf file
 *
 * @category Extension
 * @package  Calendarize
 * @author   Tim Lochmüller
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\HDNET\Autoloader\Loader::extLocalconf('HDNET', 'calendarize', \HDNET\Calendarize\Register::getDefaultAutoloader());

if (!(boolean)\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \HDNET\Calendarize\Register::extLocalconf(\HDNET\Calendarize\Register::getDefaultCalendarizeConfiguration());
    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
    $signalSlotDispatcher->connect('HDNET\\Calendarize\\Command\\ImportCommandController', 'importCommand',
        'HDNET\\Calendarize\\Slots\\EventImport', 'importCommand');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin('HDNET.calendarize', 'Calendar',
    [
        'Calendar' => 'list,latest,year,month,week,day,detail,search,result',
        'Booking' => 'booking,send'
    ],
    [
        'Calendar' => 'search,result',
        'Booking' => 'booking,send'
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\HDNET\Calendarize\Updates\CalMigrationUpdate::class] = \HDNET\Calendarize\Updates\CalMigrationUpdate::class;
