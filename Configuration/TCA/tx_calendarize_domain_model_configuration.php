<?php

/**
 * TCA Structure for Configurations
 */

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Utility\TranslateUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Configuration');

$timeType = str_replace('--palette--;LLL:EXT:hdnet/Resources/Private/Language/locallang.xlf:language;language', '',
    $base['types']['1']['showitem']);
$timeType = str_replace(',frequency',
    ',--div--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:tx_calendarize_domain_model_configuration.frequency,frequency',
    $timeType);
$timeType = str_replace(',external_ics_url', '', $timeType);
$timeType = str_replace(',groups', '', $timeType);
$timeType = str_replace(',start_date,end_date',
    ',--palette--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:date;date', $timeType);
$timeType = str_replace(',start_time,end_time,all_day',
    ',--palette--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:time;time', $timeType);
$timeType = str_replace(',counter_interval,recurrence,day',
    ',--palette--;LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:frequency_configuration;frequency_configuration',
    $timeType);

$extendTab = ',--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.extended';

$custom = array(
    'ctrl'     => array(
        'type'                    => 'type',
        'hideTable'               => true,
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
                'renderType' => 'selectSingle',
                'items'   => array(
                    array(
                        TranslateUtility::getLll('configuration.type.' . Configuration::TYPE_TIME),
                        Configuration::TYPE_TIME
                    ),
                    array(
                        TranslateUtility::getLll('configuration.type.' . Configuration::TYPE_INCLUDE_GROUP),
                        Configuration::TYPE_INCLUDE_GROUP
                    ),
                    array(
                        TranslateUtility::getLll('configuration.type.' . Configuration::TYPE_EXCLUDE_GROUP),
                        Configuration::TYPE_EXCLUDE_GROUP
                    ),
                    array(
                        TranslateUtility::getLll('configuration.type.' . Configuration::TYPE_EXTERNAL),
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
                'eval' => 'date',
                'size' => 6,
            ),
            'displayCond' => 'FIELD:type:=:' . Configuration::TYPE_TIME,
        ),
        'start_time'       => array(
            'config'      => array(
                'eval' => 'time,required',
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
                'renderType' => 'selectMultipleSideBySide',
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
                'renderType' => 'selectSingle',
                'items'   => array(
                    array(
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_NONE),
                        Configuration::FREQUENCY_NONE
                    ),
                    array(
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_DAILY),
                        Configuration::FREQUENCY_DAILY
                    ),
                    array(
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_WEEKLY),
                        Configuration::FREQUENCY_WEEKLY
                    ),
                    array(
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_MONTHLY),
                        Configuration::FREQUENCY_MONTHLY
                    ),
                    array(
                        TranslateUtility::getLll('configuration.frequency.' . Configuration::FREQUENCY_YEARLY),
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
            'config'      => array(
                'eval'    => 'trim,required',
            ),
        ),
        'day'              => array(
            'config'      => array(
                'type'    => 'select',
                'renderType' => 'selectSingle',
                'items'   => array(
                    array(
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SPECIAL_WEEKDAY),
                        Configuration::DAY_SPECIAL_WEEKDAY
                    ),
                    array(
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SPECIAL_BUSINESS),
                        Configuration::DAY_SPECIAL_BUSINESS
                    ),
                    array(
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SPECIAL_WORKDAY),
                        Configuration::DAY_SPECIAL_WORKDAY
                    ),
                    array(
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SPECIAL_WEEKEND),
                        Configuration::DAY_SPECIAL_WEEKEND
                    ),
                    array(
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_MONDAY),
                        Configuration::DAY_MONDAY
                    ),
                    array(
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_TUESDAY),
                        Configuration::DAY_TUESDAY
                    ),
                    array(
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_WEDNESDAY),
                        Configuration::DAY_WEDNESDAY
                    ),
                    array(
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_THURSDAY),
                        Configuration::DAY_THURSDAY
                    ),
                    array(
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_FRIDAY),
                        Configuration::DAY_FRIDAY
                    ),
                    array(
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SATURDAY),
                        Configuration::DAY_SATURDAY
                    ),
                    array(
                        TranslateUtility::getLll('configuration.day.' . Configuration::DAY_SUNDAY),
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
                'renderType' => 'selectSingle',
                'items'   => array(
                    array(
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_NONE),
                        Configuration::RECURRENCE_NONE
                    ),
                    array(
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_FIRST),
                        Configuration::RECURRENCE_FIRST
                    ),
                    array(
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_SECOND),
                        Configuration::RECURRENCE_SECOND
                    ),
                    array(
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_THIRD),
                        Configuration::RECURRENCE_THIRD
                    ),
                    array(
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_FOURTH),
                        Configuration::RECURRENCE_FOURTH
                    ),
                    array(
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_LAST),
                        Configuration::RECURRENCE_LAST
                    ),
                    array(
                        TranslateUtility::getLll('configuration.recurrence.' . Configuration::RECURRENCE_NEXT_TO_LAST),
                        Configuration::RECURRENCE_NEXT_TO_LAST
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