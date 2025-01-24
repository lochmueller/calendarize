<?php

namespace HDNET\Calendarize\Event;

use HDNET\Calendarize\Service\IndexerService;

final class IndexAllEvent
{
    public const POSITION_PRE = 'pre';

    public const POSITION_POST = 'post';

    public function __construct(
        private readonly IndexerService $indexerService,
        private readonly string $position,
    ) {}

    public function getIndexerService(): IndexerService
    {
        return $this->indexerService;
    }

    public function getPosition(): string
    {
        return $this->position;
    }
}
