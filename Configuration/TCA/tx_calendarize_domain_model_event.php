<?php

/**
 * TCA Structure for Events
 */

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Event');

$custom = array(
	'columns' => array(
		'title'       => array(
			'type' => 'text',
			'eval' => 'required'
		),
		'description' => array(
			'config'        => array(
				'type' => 'text',
			),
			'defaultExtras' => 'richtext:rte_transform[flag=rte_enabled|mode=ts_css]',
		),
	),
);

return \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);