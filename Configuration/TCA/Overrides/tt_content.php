<?php

declare(strict_types=1);

defined('TYPO3') or exit();

$ll = 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:';

foreach (['normal', 'special', 'booking'] as $itemGroup) {
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItemGroup(
        'tt_content',
        'list_type',
        'calendarize_' . $itemGroup,
        $ll . 'mode.' . $itemGroup,
    );
}

$pluginNameAndGroup = array_merge(
    array_fill_keys(
        ['ListDetail', 'List', 'Detail', 'Search', 'Result', 'Latest', 'Single'],
        'calendarize_normal',
    ),
    array_fill_keys(
        ['Year', 'Quarter', 'Month', 'Week', 'Day', 'Past'],
        'calendarize_special',
    ),
    ['Booking' => 'calendarize_booking'],
);

foreach ($pluginNameAndGroup as $name => $group) {
    $pluginSignature = TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'calendarize',
        $name,
        $ll . 'mode.' . strtolower($name),
        null,
        $group,
    );
    // Disable the display of layout and select_key fields for the plugin
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'pages,recursive';
    // Activate the display of the plug-in flexform field and set FlexForm definition
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        $pluginSignature,
        'FILE:EXT:calendarize/Configuration/FlexForms/Calendar.xml',
    );
}
