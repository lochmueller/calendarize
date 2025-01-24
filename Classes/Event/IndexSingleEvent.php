<?php

namespace HDNET\Calendarize\Event;

use HDNET\Calendarize\Service\IndexerService;

final class IndexSingleEvent
{
    public const POSITION_PRE = 'pre';

    public const POSITION_POST = 'post';

    public function __construct(
        private readonly string $configurationKey,
        private readonly string $tableName,
        private readonly int $uid,
        private readonly IndexerService $indexerService,
        private readonly string $position,
    ) {}

    public function getConfigurationKey(): string
    {
        return $this->configurationKey;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getUid(): int
    {
        return $this->uid;
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
