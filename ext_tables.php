<?php

/**
 * General ext_tables file.
 */
defined('TYPO3') or exit();

(function () {
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
        $GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',calendarize';
    }
})();
