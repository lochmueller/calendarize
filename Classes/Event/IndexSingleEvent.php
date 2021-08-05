<?php

namespace HDNET\Calendarize\Event;

use HDNET\Calendarize\Service\IndexerService;

final class IndexSingleEvent
{
    public const POSITION_PRE = 'pre';

    public const POSITION_POST = 'post';

    /**
     * @var string
     */
    private $configurationKey;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var int
     */
    private $uid;

    /**
     * @var IndexerService
     */
    private $indexerService;

    /**
     * @var string
     */
    private $position;

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
