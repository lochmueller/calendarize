<?php

namespace HDNET\Calendarize\Event;

final class IndexRepositoryDefaultConstraintEvent
{
    /**
     * @var array
     */
    private $indexIds;

    /**
     * @var array
     */
    private $indexTypes;

    /**
     * @var array
     */
    private $additionalSlotArguments;

    public function __construct(array $indexIds, array $indexTypes, array $additionalSlotArguments)
    {
        $this->indexIds = $indexIds;
        $this->indexTypes = $indexTypes;
        $this->additionalSlotArguments = $additionalSlotArguments;
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
