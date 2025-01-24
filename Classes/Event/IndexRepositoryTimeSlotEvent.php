<?php

namespace HDNET\Calendarize\Event;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

final class IndexRepositoryTimeSlotEvent
{
    public function __construct(
        private array $constraints,
        private readonly QueryInterface $query,
    ) {}

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
