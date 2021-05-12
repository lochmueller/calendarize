<?php

namespace HDNET\Calendarize\Event;

final class IndexRepositoryFindBySearchEvent
{
    /**
     * @var array
     */
    private $indexIds;

    private $startDate;
    private $endDate;
    private $customSearch;

    /**
     * @var array
     */
    private $indexTypes;

    /**
     * @var bool
     */
    private $emptyPreResult;

    public function __construct(array $indexIds, $startDate, $endDate, $customSearch, array $indexTypes, bool $emptyPreResult)
    {
        $this->indexIds = $indexIds;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->customSearch = $customSearch;
        $this->indexTypes = $indexTypes;
        $this->emptyPreResult = $emptyPreResult;
    }

    public function getIndexIds(): array
    {
        return $this->indexIds;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getCustomSearch()
    {
        return $this->customSearch;
    }

    public function getIndexTypes(): array
    {
        return $this->indexTypes;
    }

    public function isEmptyPreResult(): bool
    {
        return $this->emptyPreResult;
    }

    public function setIndexIds(array $indexIds): void
    {
        $this->indexIds = $indexIds;
    }

    public function setStartDate($startDate): void
    {
        $this->startDate = $startDate;
    }

    public function setEndDate($endDate): void
    {
        $this->endDate = $endDate;
    }

    public function setEmptyPreResult(bool $emptyPreResult): void
    {
        $this->emptyPreResult = $emptyPreResult;
    }
}
