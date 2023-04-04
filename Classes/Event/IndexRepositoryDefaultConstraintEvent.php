<?php

namespace HDNET\Calendarize\Event;

final class IndexRepositoryDefaultConstraintEvent
{
    public function __construct(
        private array $indexIds,
        private readonly array $indexTypes,
        private readonly array $additionalSlotArguments
    ) {
    }

    public function getIndexIds(): array
    {
        return $this->indexIds;
    }

    public function setIndexIds(array $indexIds): void
    {
        $this->indexIds = $indexIds;
    }

    public function getIndexTypes(): array
    {
        return $this->indexTypes;
    }

    public function getAdditionalSlotArguments(): array
    {
        return $this->additionalSlotArguments;
    }
}
