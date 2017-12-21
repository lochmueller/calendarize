<?php

declare(strict_types=1);

use HDNET\Autoloader\Utility\ArrayUtility;
use HDNET\Autoloader\Utility\ModelUtility;

$GLOBALS['TCA']['sys_file_reference'] = ModelUtility::getTcaOverrideInformation('calendarize', 'sys_file_reference');

$custom = [];

$GLOBALS['TCA']['sys_file_reference'] = ArrayUtility::mergeRecursiveDistinct($GLOBALS['TCA']['sys_file_reference'], $custom);
