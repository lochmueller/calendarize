<?php

namespace HDNET\Calendarize\Event;

use HDNET\Calendarize\Service\IndexerService;

final class IndexAllEvent
{
    const POSITION_PRE = 'pre';

    const POSITION_POST = 'post';

    private IndexerService $indexerService;
    private string $position;

    public function __construct(IndexerService $indexerService, string $position)
    {
        $this->indexerService = $indexerService;
        $this->position = $position;
    }

    public function getIndexerService(): IndexerService
    {
        return $this->indexerService;
    }

    public function getPosition(): string
    {
        return $this->position;
    }
}
