<?php

namespace HDNET\Calendarize\Event;

class IndexRepositoryDefaultConstraintEvent
{
    protected array $indexIds;
    protected array $indexTypes;

    public function __construct(array $indexIds, array $indexTypes)
    {
        $this->indexIds = $indexIds;
        $this->indexTypes = $indexTypes;
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
}
