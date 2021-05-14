<?php

/**
 * General ext_tables file.
 */
defined('TYPO3') or exit();

(function () {
    \HDNET\Autoloader\Loader::extTables('HDNET', 'calendarize', \HDNET\Calendarize\Register::getDefaultAutoloader());
    \HDNET\Calendarize\Register::extTables(\HDNET\Calendarize\Register::getGroupCalendarizeConfiguration());

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_calendarize_domain_model_configuration,tx_calendarize_domain_model_index');

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
        $GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',calendarize';
    }

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'calendarize',
        'web',
        'calendarize',
        '',
        [\HDNET\Calendarize\Controller\BackendController::class => 'list'],
        [
            // Additional configuration
            'access' => 'user, group',
            'icon' => 'EXT:calendarize/Resources/Public/Icons/Extension.svg',
            'iconIdentifier' => 'module-my_redirects',
            'labels' => 'LLL:EXT:calendarize/Resources/Private/Language/locallang_mod.xlf',
            'navigationComponentId' => '',
        ]
    );
})();
