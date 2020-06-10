<?php
declare(strict_types=1);

use HDNET\Calendarize\Command\CleanupCommandController;
use HDNET\Calendarize\Command\ImportCommandController;
use HDNET\Calendarize\Command\ReindexCommandController;

return [
    'calendarize:cleanup' => [
        'class' => CleanupCommandController::class,
        'schedulable' => true
    ],
    'calendarize:import' => [
        'class' => ImportCommandController::class,
        'schedulable' => true
    ],
    'calendarize:reindex' => [
        'class' => ReindexCommandController::class,
        'schedulable' => true
    ],
];
