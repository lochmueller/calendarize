<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model\Request;

use HDNET\Calendarize\Domain\Model\AbstractModel;
use HDNET\Calendarize\Domain\Model\Index;

/**
 * AbstractBookingRequest.
 */
abstract class AbstractBookingRequest extends AbstractModel
{
    protected Index $index;

    public function getIndex(): Index
    {
        return $this->index;
    }

    public function setIndex(Index $index): void
    {
        $this->index = $index;
    }
}
