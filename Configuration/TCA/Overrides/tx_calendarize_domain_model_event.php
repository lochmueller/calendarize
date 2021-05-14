<?php

declare(strict_types=1);
defined('TYPO3') or exit();

if (!(bool)\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \HDNET\Calendarize\Register::extTables(
        \HDNET\Calendarize\Register::getDefaultCalendarizeConfiguration()
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        'calendarize',
        'tx_calendarize_domain_model_event',
        'categories',
        [
            // Allow backend users to edit this record
            'exclude' => false,
        ]
    );
}
