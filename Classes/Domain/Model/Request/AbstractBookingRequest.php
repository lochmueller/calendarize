<?php

/**
 * AbstractBookingRequest.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model\Request;

use HDNET\Calendarize\Domain\Model\AbstractModel;

/**
 * AbstractBookingRequest.
 */
abstract class AbstractBookingRequest extends AbstractModel
{
    /**
     * Index.
     *
     * @var \HDNET\Calendarize\Domain\Model\Index
     */
    protected $index;

    /**
     * Get index.
     *
     * @return \HDNET\Calendarize\Domain\Model\Index
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set index.
     *
     * @param \HDNET\Calendarize\Domain\Model\Index $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }
}
