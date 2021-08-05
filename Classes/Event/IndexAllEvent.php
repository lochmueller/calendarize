<?php

namespace HDNET\Calendarize\Event;

use HDNET\Calendarize\Service\IndexerService;

final class IndexAllEvent
{
    public const POSITION_PRE = 'pre';

    public const POSITION_POST = 'post';

    private $indexerService;
    private $position;

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
