<?php

declare(strict_types=1);
defined('TYPO3') or exit();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    'calendarize',
    'tx_calendarize_domain_model_pluginconfiguration',
    'categories',
);
