<?php

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Configuration');

$custom = array(
	'ctrl'    => array(
		'type'            => 'type',
		'typeicons'       => array(
			\HDNET\Calendarize\Domain\Model\Configuration::TYPE_TIME          => '../typo3conf/ext/calendarize/Resources/Public/Icons/Configuration.png',
			\HDNET\Calendarize\Domain\Model\Configuration::TYPE_INCLUDE_GROUP => '../typo3conf/ext/calendarize/Resources/Public/Icons/ConfigurationInclude.png',
			\HDNET\Calendarize\Domain\Model\Configuration::TYPE_EXCLUDE_GROUP => '../typo3conf/ext/calendarize/Resources/Public/Icons/ConfigurationExclude.png',
		),
		'typeicon_column' => 'type',
	),
	'columns' => array(
		'type' => array(
			'config' => array(
				'type'    => 'select',
				'items'   => array(
					array(
						'Time',
						\HDNET\Calendarize\Domain\Model\Configuration::TYPE_TIME
					),
					array(
						'Include Group',
						\HDNET\Calendarize\Domain\Model\Configuration::TYPE_INCLUDE_GROUP
					),
					array(
						'Exclude Group',
						\HDNET\Calendarize\Domain\Model\Configuration::TYPE_EXCLUDE_GROUP
					),
				),
				'default' => \HDNET\Calendarize\Domain\Model\Configuration::TYPE_TIME
			)
		),
	),
	'types'   => array(
		\HDNET\Calendarize\Domain\Model\Configuration::TYPE_TIME          => array(
			'showitems' => $base['types']['1'],
		),
		\HDNET\Calendarize\Domain\Model\Configuration::TYPE_INCLUDE_GROUP => array(
			'showitems' => $base['types']['1'],
		),
		\HDNET\Calendarize\Domain\Model\Configuration::TYPE_EXCLUDE_GROUP => array(
			'showitems' => $base['types']['1'],
		),
	)
);

return \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);