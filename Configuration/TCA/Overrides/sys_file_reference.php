<?php

/**
 * Base TCA generation for the table sys_file_reference
 */

$GLOBALS['TCA']['sys_file_reference'] = \HDNET\Autoloader\Utility\ModelUtility::getTcaOverrideInformation('calendarize', 'sys_file_reference');

// custom manipulation calls here

$custom = array();

$GLOBALS['TCA']['sys_file_reference'] = \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($GLOBALS['TCA']['sys_file_reference'], $custom);