<?php
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('calendarize');

$autoloadClasses = array(
	'JMBTechnologyLimited\\ICalDissect\\ICalEvent'    => $extensionPath . 'Resources/Private/Php/ICalDissect/src/JMBTechnologyLimited/ICalDissect/ICalEvent.php',
	'JMBTechnologyLimited\\ICalDissect\\ICalExDate'   => $extensionPath . 'Resources/Private/Php/ICalDissect/src/JMBTechnologyLimited/ICalDissect/ICalExDate.php',
	'JMBTechnologyLimited\\ICalDissect\\ICalParser'   => $extensionPath . 'Resources/Private/Php/ICalDissect/src/JMBTechnologyLimited/ICalDissect/ICalParser.php',
	'JMBTechnologyLimited\\ICalDissect\\ICalTimeZone' => $extensionPath . 'Resources/Private/Php/ICalDissect/src/JMBTechnologyLimited/ICalDissect/ICalTimeZone.php',
);

return $autoloadClasses;