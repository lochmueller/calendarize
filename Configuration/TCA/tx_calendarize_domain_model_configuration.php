<?php

/**
 * TCA Structure for Configurations
 */

use HDNET\Calendarize\Domain\Model\Configuration;

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Configuration');

$typeBase = str_replace('--palette--;LLL:EXT:hdnet/Resources/Private/Language/locallang.xml:language;language', '', $base['types']['1']['showitem']);
$typeBase = str_replace(',frequency', ',--div--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xml:tx_calendarize_domain_model_configuration.frequency,frequency', $typeBase);

$custom = array(
	'ctrl'    => array(
		'type'                    => 'type',
		'hideTable'               => TRUE,
		'typeicons'               => array(
			Configuration::TYPE_TIME          => '../typo3conf/ext/calendarize/Resources/Public/Icons/Configuration.png',
			Configuration::TYPE_INCLUDE_GROUP => '../typo3conf/ext/calendarize/Resources/Public/Icons/ConfigurationInclude.png',
			Configuration::TYPE_EXCLUDE_GROUP => '../typo3conf/ext/calendarize/Resources/Public/Icons/ConfigurationExclude.png',
		),
		'typeicon_column'         => 'type',
		'requestUpdate'           => 'all_day,frequency',
		'formattedLabel_userFunc' => 'HDNET\\Calendarize\\Service\\TcaService->configurationTitle'
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
				'size' => 8,
			),
			'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
		),
		'end_date'         => array(
			'config'      => array(
				'eval' => 'required,date',
				'size' => 8,
			),
			'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
		),
		'start_time'       => array(
			'config'      => array(
				'eval' => 'time',
				'size' => 8,
			),
			'displayCond' => array(
				'AND' => array(
					'FIELD:all_day:!=:1',
					'FIELD:type:=:' . Configuration::TYPE_TIME,
				),
			),
		),
		'end_time'         => array(
			'config'      => array(
				'eval' => 'time',
				'size' => 8,
			),
			'displayCond' => array(
				'AND' => array(
					'FIELD:all_day:!=:1',
					'FIELD:type:=:' . Configuration::TYPE_TIME,
				),
			),
		),
		'all_day'          => array(
			'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
			'config'      => array(
				'default' => '0',
			),
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
			'config'      => array(
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
			),
			'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
		),
		'till_date'        => array(
			'config'      => array(
				'eval' => 'date',
				'size' => 8,
			),
			'displayCond' => array(
				'AND' => array(
					'FIELD:frequency:!=:' . Configuration::FREQUENCY_NONE,
					'FIELD:type:=:' . Configuration::TYPE_TIME,
				),
			),
		),
		'counter_amount'   => array(
			'config'      => array(
				'eval'    => 'int',
				'size'    => 5,
				'default' => 0,
			),
			'displayCond' => array(
				'AND' => array(
					'FIELD:frequency:!=:' . Configuration::FREQUENCY_NONE,
					'FIELD:type:=:' . Configuration::TYPE_TIME,
				),
			),
		),
		'counter_interval' => array(
			'config'      => array(
				'eval'    => 'int,required',
				'size'    => 5,
				'default' => '1',
			),
			'displayCond' => array(
				'AND' => array(
					'FIELD:frequency:!=:' . Configuration::FREQUENCY_NONE,
					'FIELD:type:=:' . Configuration::TYPE_TIME,
				),
			),
		),
	),
	'types'   => array(
		Configuration::TYPE_TIME          => array(
			'showitem' => $typeBase,
		),
		Configuration::TYPE_INCLUDE_GROUP => array(
			'showitem' => $typeBase,
		),
		Configuration::TYPE_EXCLUDE_GROUP => array(
			'showitem' => $typeBase,
		),
	)
);

$tca = \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);
unset($tca['types']['1']);

return $tca;