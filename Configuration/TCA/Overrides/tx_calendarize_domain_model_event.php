<?php

declare(strict_types=1);

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!(bool)ConfigurationUtility::get('disableDefaultEvent')) {
    Register::extTables(Register::getDefaultCalendarizeConfiguration());

    ExtensionManagementUtility::makeCategorizable(
        'calendarize',
        'tx_calendarize_domain_model_event',
        'categories',
        [
            // Allow backend users to edit this record
            'exclude' => false,
        ]
    );
}
