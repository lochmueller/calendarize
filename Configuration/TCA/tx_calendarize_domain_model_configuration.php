<?php

/**
 * TCA Structure for Configurations
 */

use HDNET\Calendarize\Domain\Model\Configuration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Configuration');

$timeType = str_replace('--palette--;LLL:EXT:hdnet/Resources/Private/Language/locallang.xml:language;language', '', $base['types']['1']['showitem']);
$timeType = str_replace(',frequency', ',--div--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xml:tx_calendarize_domain_model_configuration.frequency,frequency', $timeType);
$timeType = str_replace(',external_ics_url', '', $timeType);
$timeType = str_replace(',groups', '', $timeType);
$timeType = str_replace(',start_date,end_date', ',--palette--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xml:date;date', $timeType);
$timeType = str_replace(',start_time,end_time,all_day', ',--palette--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xml:time;time', $timeType);
$timeType = str_replace(',counter_interval,recurrence,day', ',--palette--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xml:frequency_configuration;frequency_configuration', $timeType);

$extendTab = ',--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.extended';

$custom = array(
	'ctrl'     => array(
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
	'columns'  => array(
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
				'size' => 6,
			),
			'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
		),
		'end_date'         => array(
			'config'      => array(
				'eval' => 'required,date',
				'size' => 6,
			),
			'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
		),
		'start_time'       => array(
			'config'      => array(
				'eval' => 'time',
				'size' => 4,
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
				'size' => 4,
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
						LocalizationUtility::translate('configuration.day.' . Configuration::DAY_SPECIAL_WEEKDAY, 'calendarize'),
						Configuration::DAY_SPECIAL_WEEKDAY
					),
					array(
						LocalizationUtility::translate('configuration.day.' . Configuration::DAY_SPECIAL_BUSINESS, 'calendarize'),
						Configuration::DAY_SPECIAL_BUSINESS
					),
					array(
						LocalizationUtility::translate('configuration.day.' . Configuration::DAY_SPECIAL_WORKDAY, 'calendarize'),
						Configuration::DAY_SPECIAL_WORKDAY
					),
					array(
						LocalizationUtility::translate('configuration.day.' . Configuration::DAY_SPECIAL_WEEKEND, 'calendarize'),
						Configuration::DAY_SPECIAL_WEEKEND
					),
					array(
						LocalizationUtility::translate('configuration.day.' . Configuration::DAY_MONDAY, 'calendarize'),
						Configuration::DAY_MONDAY
					),
					array(
						LocalizationUtility::translate('configuration.day.' . Configuration::DAY_TUESDAY, 'calendarize'),
						Configuration::DAY_TUESDAY
					),
					array(
						LocalizationUtility::translate('configuration.day.' . Configuration::DAY_WEDNESDAY, 'calendarize'),
						Configuration::DAY_WEDNESDAY
					),
					array(
						LocalizationUtility::translate('configuration.day.' . Configuration::DAY_THURSDAY, 'calendarize'),
						Configuration::DAY_THURSDAY
					),
					array(
						LocalizationUtility::translate('configuration.day.' . Configuration::DAY_FRIDAY, 'calendarize'),
						Configuration::DAY_FRIDAY
					),
					array(
						LocalizationUtility::translate('configuration.day.' . Configuration::DAY_SATURDAY, 'calendarize'),
						Configuration::DAY_SATURDAY
					),
					array(
						LocalizationUtility::translate('configuration.day.' . Configuration::DAY_SUNDAY, 'calendarize'),
						Configuration::DAY_SUNDAY
					),
				),
				'default' => Configuration::DAY_SPECIAL_WEEKDAY
			),
			'displayCond' => array(
				'OR' => array(
					'FIELD:frequency:=:' . Configuration::FREQUENCY_MONTHLY,
					'FIELD:frequency:=:' . Configuration::FREQUENCY_YEARLY,
				),
			),
		),
		'recurrence'       => array(
			'config'      => array(
				'type'    => 'select',
				'items'   => array(
					array(
						LocalizationUtility::translate('configuration.recurrence.' . Configuration::RECURRENCE_NONE, 'calendarize'),
						Configuration::RECURRENCE_NONE
					),
					array(
						LocalizationUtility::translate('configuration.recurrence.' . Configuration::RECURRENCE_FIRST, 'calendarize'),
						Configuration::RECURRENCE_FIRST
					),
					array(
						LocalizationUtility::translate('configuration.recurrence.' . Configuration::RECURRENCE_SECOND, 'calendarize'),
						Configuration::RECURRENCE_SECOND
					),
					array(
						LocalizationUtility::translate('configuration.recurrence.' . Configuration::RECURRENCE_THIRD, 'calendarize'),
						Configuration::RECURRENCE_THIRD
					),
					array(
						LocalizationUtility::translate('configuration.recurrence.' . Configuration::RECURRENCE_FOURTH, 'calendarize'),
						Configuration::RECURRENCE_FOURTH
					),
					array(
						LocalizationUtility::translate('configuration.recurrence.' . Configuration::RECURRENCE_LAST, 'calendarize'),
						Configuration::RECURRENCE_LAST
					),
				),
				'default' => Configuration::RECURRENCE_NONE
			),
			'displayCond' => array(
				'OR' => array(
					'FIELD:frequency:=:' . Configuration::FREQUENCY_MONTHLY,
					'FIELD:frequency:=:' . Configuration::FREQUENCY_YEARLY,
				),
			),
		),
	),
	'palettes' => array(
		'date'                    => array(
			'canNotCollapse' => 1,
			'showitem'       => 'start_date,end_date',
		),
		'time'                    => array(
			'canNotCollapse' => 1,
			'showitem'       => 'start_time,end_time,--linebreak--,all_day',
		),
		'termination_condition'   => array(
			'canNotCollapse' => 1,
			'showitem'       => 'till_date,counter_amount',
		),
		'frequency_configuration' => array(
			'canNotCollapse' => 1,
			'showitem'       => 'counter_interval,recurrence,day',
		),
	),
	'types'    => array(
		Configuration::TYPE_TIME          => array(
			'showitem' => $timeType,
		),
		Configuration::TYPE_INCLUDE_GROUP => array(
			'showitem' => 'type,groups' . $extendTab,
		),
		Configuration::TYPE_EXCLUDE_GROUP => array(
			'showitem' => 'type,groups' . $extendTab,
		),
		Configuration::TYPE_EXTERNAL      => array(
			'showitem' => 'type,external_ics_url' . $extendTab,
		),
	)
);

$tca = \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);
unset($tca['types']['1']);

return $tca;