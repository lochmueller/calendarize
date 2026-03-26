<?php

declare(strict_types=1);

defined('TYPO3') or exit();

$ll = 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:';

foreach (['normal', 'special', 'booking'] as $itemGroup) {
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItemGroup(
        'tt_content',
        'CType',
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

foreach ($pluginNameAndGroup as $pluginName => $group) {
    $pluginSignature = TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'calendarize',
        $pluginName,
        $ll . 'mode.' . strtolower($pluginName),
        null,
        $group,
    );
    // Disable the display of layout and select_key fields for the plugin
//    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'pages,recursive';
    // Activate the display of the plug-in flexform field and set FlexForm definition
//    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

//    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
//        $pluginSignature,
//        'FILE:EXT:calendarize/Configuration/FlexForms/Calendar.xml',
//    );

    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        '--div--;Configuration,pi_flexform,',
        $pluginSignature,
        'after:subheader',
    );

    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:calendarize/Configuration/FlexForms/Calendar.xml',
        $pluginSignature,
    );
}
