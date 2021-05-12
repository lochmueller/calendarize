<?php

namespace HDNET\Calendarize\Event;

use HDNET\Calendarize\Service\IndexerService;

final class IndexSingleEvent
{
    const POSITION_PRE = 'pre';

    const POSITION_POST = 'post';

    private string $configurationKey;

    private string $tableName;

    private int $uid;

    private IndexerService $indexerService;

    private string $position;

    public function __construct(string $configurationKey, string $tableName, int $uid, IndexerService $indexerService, string $position)
    {
        $this->configurationKey = $configurationKey;
        $this->tableName = $tableName;
        $this->uid = $uid;
        $this->indexerService = $indexerService;
        $this->position = $position;
    }

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
