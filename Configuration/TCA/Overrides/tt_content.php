<?php

declare(strict_types=1);

defined('TYPO3') or exit();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'calendarize',
    'Calendar',
    \HDNET\Calendarize\Utility\TranslateUtility::getLll('pluginName')
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['calendarize_calendar'] = 'layout, select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['calendarize_calendar'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'calendarize_calendar',
    'FILE:EXT:calendarize/Configuration/FlexForms/Calendar.xml'
);

if (!\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_calendarize_domain_model_event');
}
