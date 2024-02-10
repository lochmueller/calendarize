<?php

declare(strict_types=1);

use HDNET\Calendarize\Register;

defined('TYPO3') or exit();

Register::createTcaConfiguration(Register::getDefaultCalendarizeConfiguration());

if (!HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_calendarize_domain_model_event');
} else {
    $GLOBALS['TCA']['tx_calendarize_domain_model_event']['ctrl']['hideTable'] = true;
}
