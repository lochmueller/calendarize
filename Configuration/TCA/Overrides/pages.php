<?php

// module icon
$relIconPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('calendarize') . 'ext_icon.png';
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
		2 => $relIconPath
	);
}