<?php

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Service\SecondaryTimeTableService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$flexForms = [];

/** @var SecondaryTimeTableService $secondaryTimeTableService */
$secondaryTimeTableService = GeneralUtility::makeInstance(SecondaryTimeTableService::class);
$services = $secondaryTimeTableService->getSecondaryTimeTables();

if (empty($services)) {
    return;
}

ExtensionManagementUtility::addTcaSelectItemGroup(
    'tx_calendarize_domain_model_configuration',
    'type',
    'secondary',
    'Secondary',
);
foreach ($services as $service) {
    $timeTable = [
        'label' => $service->getLabel(),
        'value' => $service->getIdentifier(),
        'group' => 'secondary',
    ];
    $flexForms[$service->getIdentifier()] = $service->getFlexForm();
    ExtensionManagementUtility::addTcaSelectItem(
        'tx_calendarize_domain_model_configuration',
        'type',
        $timeTable,
    );
    $GLOBALS['TCA']['tx_calendarize_domain_model_configuration']['ctrl']['typeicon_classes'][$service->getIdentifier()] = 'apps-calendarize-type-' . Configuration::TYPE_TIME;
    $GLOBALS['TCA']['tx_calendarize_domain_model_configuration']['types'][$service->getIdentifier()]['showitem'] = $service->getTcaServiceTypeFields();
}
ExtensionManagementUtility::addTCAcolumns(
    'tx_calendarize_domain_model_configuration',
    [
        'flex_form' => [
            'label' => 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:tx_calendarize_domain_model_configuration.flex_form',
            'config' => [
                'type' => 'flex',
                'ds_pointerField' => 'type',
                'ds' => $flexForms,
            ],
        ],
    ],
);
