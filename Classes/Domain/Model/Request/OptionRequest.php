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
     * @return array
     */
    public function __sleep()
    {
        return ['sorting', 'direction', 'pid'];
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
}
