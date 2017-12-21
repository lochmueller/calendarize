<?php

declare(strict_types=1);

$icon = 'apps-pagetree-folder-contains-calendarize';

$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
    0 => 'Calendarize',
    1 => 'calendar',
    2 => $icon,
];
