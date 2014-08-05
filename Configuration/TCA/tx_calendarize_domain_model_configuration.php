<?php

/**
 * TCA Structure for Configurations
 */

use HDNET\Calendarize\Domain\Model\Configuration;

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Configuration');

$custom = array(
	'ctrl'    => array(
		'type'            => 'type',
		'hideTable'       => TRUE,
		'typeicons'       => array(
			Configuration::TYPE_TIME          => '../typo3conf/ext/calendarize/Resources/Public/Icons/Configuration.png',
			Configuration::TYPE_INCLUDE_GROUP => '../typo3conf/ext/calendarize/Resources/Public/Icons/ConfigurationInclude.png',
			Configuration::TYPE_EXCLUDE_GROUP => '../typo3conf/ext/calendarize/Resources/Public/Icons/ConfigurationExclude.png',
		),
		'typeicon_column' => 'type',
	),
	'columns' => array(
		'type'       => array(
			'config' => array(
				'type'    => 'select',
				'items'   => array(
					array(
						'Time',
						Configuration::TYPE_TIME
					),
					array(
						'Include Group',
						Configuration::TYPE_INCLUDE_GROUP
					),
					array(
						'Exclude Group',
						Configuration::TYPE_EXCLUDE_GROUP
					),
				),
				'default' => Configuration::TYPE_TIME
			)
		),
		'start_date' => array(
			'config' => array(
				'eval' => 'required,date',
			),
		),
		'start_time' => array(
			'config' => array(
				'eval' => 'time',
			),
		),
		'end_date'   => array(
			'config' => array(
				'eval' => 'required,date',
			),
		),
		'end_time' => array(
			'config' => array(
				'eval' => 'time',
			),
		),
	),
	'types'   => array(
		Configuration::TYPE_TIME          => array(
			'showitem' => $base['types']['1']['showitem'],
		),
		Configuration::TYPE_INCLUDE_GROUP => array(
			'showitem' => $base['types']['1']['showitem'],
		),
		Configuration::TYPE_EXCLUDE_GROUP => array(
			'showitem' => $base['types']['1']['showitem'],
		),
	)
);

$tca = \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);
unset($tca['types']['1']);

return $tca;