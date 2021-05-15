<?php

declare(strict_types=1);
defined('TYPO3') or exit();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'calendarize',
    'Calendar',
    \HDNET\Calendarize\Utility\TranslateUtility::getLll('pluginName')
);

if (!\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_calendarize_domain_model_event');
}
