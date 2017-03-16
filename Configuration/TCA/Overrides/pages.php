<?php

use HDNET\Autoloader\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$icon = 'apps-pagetree-folder-contains-calendarize';

$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
    0 => 'Calendarize',
    1 => 'calendar',
    2 => $icon
];
