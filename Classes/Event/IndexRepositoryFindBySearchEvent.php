<?php

namespace HDNET\Calendarize\Event;

final class IndexRepositoryFindBySearchEvent
{
    private array $foreignIds = [];

    public function __construct(
        protected ?\DateTimeInterface $startDate,
        protected ?\DateTimeInterface $endDate,
        protected array $customSearch,
        protected array $indexTypes,
        protected bool $emptyPreResult,
    ) {}

    public function getForeignIds(): array
    {
        return $this->foreignIds;
    }

    public function setForeignIds(array $foreignIds): void
    {
        $this->foreignIds = $foreignIds;
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getCustomSearch(): array
    {
        return $this->customSearch;
    }

    public function setCustomSearch(array $customSearch): void
    {
        $this->customSearch = $customSearch;
    }

    public function getIndexTypes(): array
    {
        return $this->indexTypes;
    }

    public function setIndexTypes(array $indexTypes): void
    {
        $this->indexTypes = $indexTypes;
    }

    public function isEmptyPreResult(): bool
    {
        return $this->emptyPreResult;
    }

    public function setEmptyPreResult(bool $emptyPreResult): void
    {
        $this->emptyPreResult = $emptyPreResult;
    }
}
