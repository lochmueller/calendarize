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
    /**
     * @var ConstraintInterface[]
     */
    private $constraints;

    /**
     * @var QueryInterface
     */
    private $query;

    /**
     * @var array
     */
    private $additionalArguments;

    /**
     * @var \DateTimeInterface|null
     */
    private $start;

    /**
     * @var \DateTimeInterface|null
     */
    private $end;

    /**
     * AddTimeFrameConstraintsEvent constructor.
     *
     * @param ConstraintInterface[]   $constraints
     * @param QueryInterface          $query
     * @param array                   $additionalArguments
     * @param \DateTimeInterface|null $start
     * @param \DateTimeInterface|null $end
     */
    public function __construct(
        array &$constraints,
        QueryInterface $query,
        array $additionalArguments,
        ?\DateTimeInterface $start,
        ?\DateTimeInterface $end
    ) {
        $this->constraints = &$constraints;
        $this->query = $query;
        $this->additionalArguments = $additionalArguments;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return ConstraintInterface[]
     */
    public function &getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * @return QueryInterface
     */
    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getAdditionalArguments(): array
    {
        return $this->additionalArguments;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    /**
     * @param \DateTimeInterface|null $start
     */
    public function setStart(?\DateTimeInterface $start): void
    {
        $this->start = $start;
    }

    /**
     * @param \DateTimeInterface|null $end
     */
    public function setEnd(?\DateTimeInterface $end): void
    {
        $this->end = $end;
    }
}
