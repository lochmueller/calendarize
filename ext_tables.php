<?php

/**
 * General ext_tables file.
 */

defined('TYPO3') or exit();

use HDNET\Calendarize\Domain\Model\Event;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

(function () {
    $GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize']['Event'] = [
        'uniqueRegisterKey' => 'Event',
        'title' => 'Calendarize Event',
        'modelName' => Event::class,
        'partialIdentifier' => 'Event',
        'tableName' => 'tx_calendarize_domain_model_event',
        'required' => true,
    ];

    if (ExtensionManagementUtility::isLoaded('ke_search')) {
        $GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',calendarize';
    }
})();
