<?php

declare(strict_types=1);
defined('TYPO3') or exit();

(function () {
    $icon = 'apps-pagetree-folder-contains-calendarize';

    $GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
        0 => 'Calendarize',
        1 => 'calendarize',
        2 => $icon,
    ];
    $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-calendarize'] = $icon;
})();
