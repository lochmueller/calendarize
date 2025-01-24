<?php

namespace HDNET\Calendarize\Event;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

final class ModifyDateTimeFrameConstraintEvent
{
    public function __construct(
        protected QueryInterface $query,
        protected ?\DateTimeInterface $start,
        protected ?\DateTimeInterface $end,
        protected bool $respectTime,
        protected array $dateConstraints,
    ) {}

    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function isRespectTime(): bool
    {
        return $this->respectTime;
    }

    public function getDateConstraints(): array
    {
        return $this->dateConstraints;
    }

    public function setDateConstraints(array $dateConstraints): void
    {
        $this->dateConstraints = $dateConstraints;
    }
}
