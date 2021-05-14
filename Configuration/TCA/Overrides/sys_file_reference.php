<?php

declare(strict_types=1);
defined('TYPO3') or exit();

$GLOBALS['TCA']['sys_file_reference'] = \HDNET\Autoloader\Utility\ModelUtility::getTcaOverrideInformation('calendarize', 'sys_file_reference');

$custom = [];

$GLOBALS['TCA']['sys_file_reference'] = \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($GLOBALS['TCA']['sys_file_reference'], $custom);
