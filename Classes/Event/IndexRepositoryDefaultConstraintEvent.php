<?php

namespace HDNET\Calendarize\Event;

final class IndexRepositoryDefaultConstraintEvent
{
    public function __construct(
        private array $foreignIds,
        private readonly array $indexTypes,
        private readonly array $additionalSlotArguments,
    ) {}

    public function getForeignIds(): array
    {
        return $this->foreignIds;
    }

    public function setForeignIds(array $foreignIds): void
    {
        $this->foreignIds = $foreignIds;
    }

    public function getIndexTypes(): array
    {
        return $this->indexTypes;
    }

    public function getAdditionalSlotArguments(): array
    {
        return $this->additionalSlotArguments;
    }

    /**
     * @deprecated use {@see getForeignIds} instead
     */
    public function getIndexIds(): array
    {
        return $this->getForeignIds();
    }

    /**
     * @deprecated use {@see setForeignIds} instead
     */
    public function setIndexIds(array $indexIds): void
    {
        $this->setForeignIds($indexIds);
    }
}
