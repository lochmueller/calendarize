<?php

/**
 * TCA Structure for ConfigurationGroups
 */

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\ConfigurationGroup');

$custom = array(
	'ctrl'    => array(
		'searchFields' => 'uid,title',
	),
	'columns' => array(
		'configurations' => array(
			'config' => array(
				'type'          => 'inline',
				'foreign_table' => 'tx_calendarize_domain_model_configuration',
				'minitems'      => 1,
			)
		),
	),
);

return \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);