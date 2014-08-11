<?php

/**
 * TCA Structure for Index
 */

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Index');


$custom = array(
	'ctrl'    => array(
		'hideTable' => TRUE,
	),
	'columns' => array(
		'unique_register_key' => array(
			'config' => array(
				'type' => 'none',
			)
		),
		'foreign_uid'         => array(
			'config' => array(
				'type' => 'none',
			),
		),
		'start_date'          => array(
			'config' => array(
				'eval' => 'required,date',
			),
		),
		'end_date'            => array(
			'config' => array(
				'eval' => 'required,date',
			),
		),
	),
);

return \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);