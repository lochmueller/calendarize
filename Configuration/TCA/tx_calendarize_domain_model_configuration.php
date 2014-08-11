<?php

/**
 * TCA Structure for Configurations
 */

use HDNET\Calendarize\Domain\Model\Configuration;

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Configuration');

$custom = array(
	'ctrl'    => array(
		'type'            => 'type',
		#'hideTable'       => TRUE,
		'typeicons'       => array(
			Configuration::TYPE_TIME          => '../typo3conf/ext/calendarize/Resources/Public/Icons/Configuration.png',
			Configuration::TYPE_INCLUDE_GROUP => '../typo3conf/ext/calendarize/Resources/Public/Icons/ConfigurationInclude.png',
			Configuration::TYPE_EXCLUDE_GROUP => '../typo3conf/ext/calendarize/Resources/Public/Icons/ConfigurationExclude.png',
		),
		'typeicon_column' => 'type',
		'requestUpdate'   => 'all_day,frequency',
	),
	'columns' => array(
		'type'             => array(
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
		'start_date'       => array(
			'config'      => array(
				'eval' => 'required,date',
			),
			'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
		),
		'end_date'         => array(
			'config'      => array(
				'eval' => 'required,date',
			),
			'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
		),
		'start_time'       => array(
			'config'      => array(
				'eval' => 'time',
			),
			'displayCond' => array(
				'AND' => array(
					'FIELD:allday:!=:1',
					'FIELD:type:=:' . Configuration::TYPE_TIME,
				),
			),
		),
		'end_time'         => array(
			'config'      => array(
				'eval' => 'time',
			),
			'displayCond' => array(
				'AND' => array(
					'FIELD:allday:!=:1',
					'FIELD:type:=:' . Configuration::TYPE_TIME,
				),
			),
		),
		'all_day'          => array(
			'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
		),
		'groups'           => array(
			'config'      => array(
				'type'          => 'select',
				'foreign_table' => 'tx_calendarize_domain_model_configurationgroup',
				'minitems'      => '1',
				'size'          => 5,
				'maxitems'      => '99',
			),
			'displayCond' => 'FIELD:type:!=:' . Configuration::TYPE_TIME,
		),
		'frequency'        => array(
			'config' => array(
				'type'    => 'select',
				'items'   => array(
					array(
						'None',
						Configuration::FREQUENCY_NONE
					),
					array(
						'Daily',
						Configuration::FREQUENCY_DAILY
					),
					array(
						'Weekly',
						Configuration::FREQUENCY_WEEKLY
					),
					array(
						'Monthly',
						Configuration::FREQUENCY_MONTHLY
					),
					array(
						'Yearly',
						Configuration::FREQUENCY_YEARLY
					),
				),
				'default' => Configuration::FREQUENCY_NONE
			)
		),
		'till_date'        => array(
			'displayCond' => 'FIELD:type:REQ',
		),
		'counter_amount'   => array(
			'displayCond' => 'FIELD:type:REQ',
		),
		'counter_interval' => array(
			'displayCond' => 'FIELD:type:REQ',
			'config'      => array(
				'default' => 1
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