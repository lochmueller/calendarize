<?php

declare(strict_types=1);

defined('TYPO3') or exit();

$temporaryColumns = [
    'import_id' => [
        'label' => 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:sys_category.import_id',
        'exclude' => true,
        'config' => [
            'type' => 'input',
        ],
    ],
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_category', $temporaryColumns);
