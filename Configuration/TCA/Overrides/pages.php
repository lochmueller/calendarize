<?php

use HDNET\Autoloader\Utility\IconUtility;

$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
    0 => 'Calendarize',
    1 => 'calendar',
    2 => \TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.0') ? 'apps-pagetree-folder-contains-calendarize' : IconUtility::getByExtensionKey('calendarize',
        true)
];