<?php

declare(strict_types=1);

defined('TYPO3') or exit();

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'calendarize',
    'Configuration/TypoScript/',
    'Calendarize',
);
