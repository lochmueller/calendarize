<?php

declare(strict_types=1);

use HDNET\Autoloader\Utility\ArrayUtility;
use HDNET\Autoloader\Utility\ModelUtility;

$GLOBALS['TCA']['sys_category'] = ModelUtility::getTcaOverrideInformation('calendarize', 'sys_category');

$custom = [];

$GLOBALS['TCA']['sys_category'] = ArrayUtility::mergeRecursiveDistinct($GLOBALS['TCA']['sys_category'], $custom);
