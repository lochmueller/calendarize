<?php

/**
 * General ext_tables file.
 */

defined('TYPO3') or exit();

(function () {
    $GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize']['Event'] = [
        'uniqueRegisterKey' => 'Event',
        'title' => 'Calendarize Event',
        'modelName' => \HDNET\Calendarize\Domain\Model\Event::class,
        'partialIdentifier' => 'Event',
        'tableName' => 'tx_calendarize_domain_model_event',
        'required' => true,
    ];

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
        $GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',calendarize';
    }
})();
