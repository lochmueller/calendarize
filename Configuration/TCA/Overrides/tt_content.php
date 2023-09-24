<?php

declare(strict_types=1);

defined('TYPO3') or exit();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItemGroup(
    'tt_content',
    'list_type',
    'calendarize_normal',
    'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:mode.normal'
);

foreach (['ListDetail', 'List', 'Detail', 'Search', 'Result', 'Latest', 'Single'] as $name) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'calendarize',
        $name,
        'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:mode.' . lcfirst($name),
        null,
        'calendarize_normal'
    );

    $pluginName = 'calendarize_' . lcfirst($name);
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginName] = 'layout, select_key';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginName] = 'pi_flexform';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        $pluginName,
        'FILE:EXT:calendarize/Configuration/FlexForms/Calendar.xml'
    );
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItemGroup(
    'tt_content',
    'list_type',
    'calendarize_special',
    'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:mode.special'
);

foreach (['Year', 'Quarter', 'Month', 'Week', 'Day', 'Past'] as $name) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'calendarize',
        $name,
        'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:mode.' . lcfirst($name),
        null,
        'calendarize_special'
    );

    $pluginName = 'calendarize_' . lcfirst($name);
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginName] = 'layout, select_key';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginName] = 'pi_flexform';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        $pluginName,
        'FILE:EXT:calendarize/Configuration/FlexForms/Calendar.xml'
    );
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItemGroup(
    'tt_content',
    'list_type',
    'calendarize_booking',
    'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:mode.booking'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'calendarize',
    'Booking',
    'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:mode.booking',
    null,
    'calendarize_booking'
);

$pluginName = 'calendarize_booking';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginName] = 'layout, select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginName] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginName,
    'FILE:EXT:calendarize/Configuration/FlexForms/Calendar.xml'
);

if (!\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_calendarize_domain_model_event');
}
