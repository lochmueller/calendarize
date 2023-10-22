<?php

declare(strict_types=1);

defined('TYPO3') or exit();

if (!\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_calendarize_domain_model_event');

    $GLOBALS['TCA']['tx_calendarize_domain_model_event']['columns']['calendarize'] = [
        'label' => 'Calendarize',
        'l10n_mode' => 'exclude',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_calendarize_domain_model_configuration',
            'minitems' => 1,
            'maxitems' => 99,
            'behaviour' => [
                'enableCascadingDelete' => true,
            ],
        ],
    ];
    $GLOBALS['TCA']['tx_calendarize_domain_model_event']['columns']['calendarize_info'] = [
        'label' => 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:tca.information',
        'config' => [
            'type' => 'none',
            'renderType' => 'calendarizeInfoElement',
            'parameters' => [
                'items' => 10,
            ],
        ],
    ];
}
