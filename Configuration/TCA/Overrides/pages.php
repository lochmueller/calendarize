<?php

// module icon
$relIconPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('calendarize') . 'ext_icon.png';
$addCalendarizeToModuleSelection = true;
foreach ($GLOBALS['TCA']['pages']['columns']['module']['config']['items'] as $item) {
    if ($item[1] === 'calendar') {
        $addCalendarizeToModuleSelection = false;
        continue;
    }
}
if ($addCalendarizeToModuleSelection) {
    $GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = array(
        0 => 'Calendarize',
        1 => 'calendar',
        2 => $relIconPath
    );
}