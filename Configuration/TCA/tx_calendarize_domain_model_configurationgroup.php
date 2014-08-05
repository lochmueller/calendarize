<?php

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\ConfigurationGroup');

$custom = array(
	'columns' => array(
		'configurations' => array(
			'config' => array(
				'type'          => 'inline',
				'foreign_table' => 'tx_calendarize_domain_model_configuration',
			)
		),
	),
);

return \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);