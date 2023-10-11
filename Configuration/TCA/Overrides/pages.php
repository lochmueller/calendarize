<?php

declare(strict_types=1);

defined('TYPO3') or exit();

(function () {
    $icon = 'apps-pagetree-folder-contains-calendarize';

    $GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
        'label' => 'Calendarize',
        'value' => 'calendarize',
        'icon' => $icon,
    ];
    $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-calendarize'] = $icon;
})();
