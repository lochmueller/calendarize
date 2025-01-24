<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Event;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * This event contains the parameters for the creation of the date time constraints and allows
 * the modification of the start and end.
 */
final class AddTimeFrameConstraintsEvent
{
    public function __construct(
        private readonly array $constraints,
        private readonly QueryInterface $query,
        private readonly array $additionalArguments,
        private ?\DateTimeInterface $start,
        private ?\DateTimeInterface $end,
    ) {}

    /**
     * @return ConstraintInterface[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    public function getAdditionalArguments(): array
    {
        return $this->additionalArguments;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setStart(?\DateTimeInterface $start): void
    {
        $this->start = $start;
    }

    public function setEnd(?\DateTimeInterface $end): void
    {
        $this->end = $end;
    }
}
