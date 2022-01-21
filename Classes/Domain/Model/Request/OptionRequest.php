<?php

/**
 * OptionRequest.
 */

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model\Request;

use HDNET\Calendarize\Domain\Model\AbstractModel;

/**
 * OptionRequest.
 */
class OptionRequest extends AbstractModel
{
    /**
     * Sorting.
     *
     * @var string
     */
    protected $sorting = 'start_date';

    /**
     * Direction.
     *
     * @var string
     */
    protected $direction = 'asc';

    /**
     * Type.
     *
     * @var string
     */
    protected $type = '';

    /**
     * @var \DateTime|null
     */
    protected $startDate;

    /**
     * @var \DateTime|null
     */
    protected $endDate;

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['sorting', 'direction', 'pid', 'type', 'startDate', 'endDate'];
    }

    /**
     * @return string
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param string $sorting
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime|null $startDate
     */
    public function setStartDate(?\DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime|null $endDate
     */
    public function setEndDate(?\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }
}
