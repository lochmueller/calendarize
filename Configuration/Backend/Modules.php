<?php

return [
    'web_calendarize' => [
        'parent' => 'web',
        'access' => 'user',
        'path' => '/module/web/calendarize',
        'iconIdentifier' => 'ext-calendarize-wizard-icon',
        'labels' => 'LLL:EXT:calendarize/Resources/Private/Language/locallang_mod.xlf',
        'inheritNavigationComponentFromMainModule' => false,
        'extensionName' => 'calendarize',
        'controllerActions' => [
            \HDNET\Calendarize\Controller\BackendController::class => ['list'],
        ],
        'aliases' => ['web_CalendarizeCalendarize'],
    ],
];
