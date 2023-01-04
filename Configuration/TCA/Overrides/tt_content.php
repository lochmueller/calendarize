<?php

declare(strict_types=1);

use HDNET\Autoloader\Utility\ModelUtility;

defined('TYPO3') or exit();

$GLOBALS['TCA']['tt_content'] = ModelUtility::getTcaOverrideInformation('calendarize', 'tt_content');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'calendarize',
    'Calendar',
    \HDNET\Calendarize\Utility\TranslateUtility::getLll('pluginName')
);

if (!\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_calendarize_domain_model_event');
}
