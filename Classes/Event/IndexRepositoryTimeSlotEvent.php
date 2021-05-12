<?php

namespace HDNET\Calendarize\Event;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class IndexRepositoryTimeSlotEvent
{
    protected array $constraints;
    protected QueryInterface $query;

    public function __construct(array $constraints, QueryInterface $query)
    {
        $this->constraints = $constraints;
        $this->query = $query;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    public function setConstraints(array $constraints): void
    {
        $this->constraints = $constraints;
    }
}
