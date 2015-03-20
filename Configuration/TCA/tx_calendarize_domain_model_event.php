<?php

/**
 * TCA Structure for Events
 */

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Event');

$custom = array(
	'ctrl'    => array(
		'hideTable'    => (boolean)\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent'),
		'searchFields' => 'uid,title,description',
		'thumbnail'    => 'images',
	),
	'columns' => array(
		'title' => array(
			'type' => 'text',
			'eval' => 'required'
		),
	),
);

return \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);