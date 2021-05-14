<?php

declare(strict_types=1);
defined('TYPO3') or exit();

$GLOBALS['TCA']['sys_category'] = \HDNET\Autoloader\Utility\ModelUtility::getTcaOverrideInformation('calendarize', 'sys_category');

$custom = [];

$GLOBALS['TCA']['sys_category'] = \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($GLOBALS['TCA']['sys_category'], $custom);
