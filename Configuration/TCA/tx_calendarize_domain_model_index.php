<?php

/**
 * TCA Structure for Index
 */

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Index');


$custom = array(
	'ctrl' => array( #	'hideTable' => TRUE,
	),
);

return \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);