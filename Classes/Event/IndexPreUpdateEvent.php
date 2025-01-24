<?php

namespace HDNET\Calendarize\Event;

final class IndexPreUpdateEvent
{
    public function __construct(
        private array $neededItems,
        private readonly string $tableName,
        private readonly int $uid,
    ) {}

    public function getNeededItems(): array
    {
        return $this->neededItems;
    }

    public function setNeededItems(array $neededItems): void
    {
        $this->neededItems = $neededItems;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getUid(): int
    {
        return $this->uid;
    }
}
