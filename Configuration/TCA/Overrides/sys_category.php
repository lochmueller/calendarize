<?php

/**
 * Base TCA generation for the table sys_category
 */

$GLOBALS['TCA']['sys_category'] = \HDNET\Autoloader\Utility\ModelUtility::getTcaOverrideInformation('calendarize', 'sys_category');

// custom manipulation calls here

$custom = array();

$GLOBALS['TCA']['sys_category'] = \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($GLOBALS['TCA']['sys_category'], $custom);