<?php

/**
 * TCA Structure for Configurations
 */

use HDNET\Calendarize\Domain\Model\Configuration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Configuration');

$typeBase = str_replace('--palette--;LLL:EXT:hdnet/Resources/Private/Language/locallang.xml:language;language', '', $base['types']['1']['showitem']);
$typeBase = str_replace(',frequency', ',--div--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xml:tx_calendarize_domain_model_configuration.frequency,frequency', $typeBase);
$typeBase = str_replace(',external_ics_url', '', $typeBase);

$custom = array(
	'ctrl'    => array(
		'type'                    => 'type',
		'hideTable'               => TRUE,
		'typeicons'               => array(
			Configuration::TYPE_TIME          => ExtensionManagementUtility::extRelPath('calendarize') . 'Resources/Public/Icons/Configuration.png',
			Configuration::TYPE_INCLUDE_GROUP => ExtensionManagementUtility::extRelPath('calendarize') . 'Resources/Public/Icons/ConfigurationInclude.png',
			Configuration::TYPE_EXCLUDE_GROUP => ExtensionManagementUtility::extRelPath('calendarize') . 'Resources/Public/Icons/ConfigurationExclude.png',
			Configuration::TYPE_EXTERNAL      => ExtensionManagementUtility::extRelPath('calendarize') . 'Resources/Public/Icons/ConfigurationExternal.png',
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
						LocalizationUtility::translate('configuration.type.' . Configuration::TYPE_TIME, 'calendarize'),
						Configuration::TYPE_TIME
					),
					array(
						LocalizationUtility::translate('configuration.type.' . Configuration::TYPE_INCLUDE_GROUP, 'calendarize'),
						Configuration::TYPE_INCLUDE_GROUP
					),
					array(
						LocalizationUtility::translate('configuration.type.' . Configuration::TYPE_EXCLUDE_GROUP, 'calendarize'),
						Configuration::TYPE_EXCLUDE_GROUP
					),
					array(
						LocalizationUtility::translate('configuration.type.' . Configuration::TYPE_EXTERNAL, 'calendarize'),
						Configuration::TYPE_EXTERNAL
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
						LocalizationUtility::translate('configuration.frequency.' . Configuration::FREQUENCY_NONE, 'calendarize'),
						Configuration::FREQUENCY_NONE
					),
					array(
						LocalizationUtility::translate('configuration.frequency.' . Configuration::FREQUENCY_DAILY, 'calendarize'),
						Configuration::FREQUENCY_DAILY
					),
					array(
						LocalizationUtility::translate('configuration.frequency.' . Configuration::FREQUENCY_WEEKLY, 'calendarize'),
						Configuration::FREQUENCY_WEEKLY
					),
					array(
						LocalizationUtility::translate('configuration.frequency.' . Configuration::FREQUENCY_MONTHLY, 'calendarize'),
						Configuration::FREQUENCY_MONTHLY
					),
					array(
						LocalizationUtility::translate('configuration.frequency.' . Configuration::FREQUENCY_YEARLY, 'calendarize'),
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
		'external_ics_url' => array(
			'displayCond' => array(
				'FIELD:type:=:' . Configuration::TYPE_EXTERNAL,
			),
		),
		'day'              => array(
			'config'      => array(
				'type'    => 'select',
				'items'   => array(
					array(
						'',
						''
					),
					array(
						Configuration::DAY_SPECIAL_BUSINESS,
						Configuration::DAY_SPECIAL_BUSINESS
					),
					array(
						Configuration::DAY_SPECIAL_WEEKDAY,
						Configuration::DAY_SPECIAL_WEEKDAY
					),
					array(
						Configuration::DAY_SPECIAL_WORKDAY,
						Configuration::DAY_SPECIAL_WORKDAY
					),
					array(
						Configuration::DAY_MONDAY,
						Configuration::DAY_MONDAY
					),
					array(
						Configuration::DAY_TUESDAY,
						Configuration::DAY_TUESDAY
					),
					array(
						Configuration::DAY_WEDNESDAY,
						Configuration::DAY_WEDNESDAY
					),
					array(
						Configuration::DAY_THURSDAY,
						Configuration::DAY_THURSDAY
					),
					array(
						Configuration::DAY_FRIDAY,
						Configuration::DAY_FRIDAY
					),
					array(
						Configuration::DAY_SATURDAY,
						Configuration::DAY_SATURDAY
					),
					array(
						Configuration::DAY_SUNDAY,
						Configuration::DAY_SUNDAY
					),
				),
				'default' => ''

			),
			'displayCond' => 'FIELD:frequency:!=:' . Configuration::FREQUENCY_NONE,
		),
		'recurrence'       => array(
			'config'      => array(
				'type'    => 'select',
				'items'   => array(
					array(
						'',
						''
					),
					array(
						Configuration::RECURRENCE_FIRST,
						Configuration::RECURRENCE_FIRST
					),
					array(
						Configuration::RECURRENCE_SECOND,
						Configuration::RECURRENCE_SECOND
					),
					array(
						Configuration::RECURRENCE_THIRD,
						Configuration::RECURRENCE_THIRD
					),
					array(
						Configuration::RECURRENCE_FOURTH,
						Configuration::RECURRENCE_FOURTH
					),
					array(
						Configuration::RECURRENCE_LAST,
						Configuration::RECURRENCE_LAST
					),
				),
				'default' => ''
			),
			'displayCond' => 'FIELD:frequency:!=:' . Configuration::FREQUENCY_NONE,
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
		Configuration::TYPE_EXTERNAL      => array(
			'showitem' => 'type,external_ics_url,--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.extended'
		),
	)
);

$tca = \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);
unset($tca['types']['1']);

return $tca;