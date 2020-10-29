<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service\Ical;

use HDNET\Calendarize\Exception\UnableToGetEventsException;
use HDNET\Calendarize\Ical\ICalEvent;

interface ICalServiceInterface
{
    /**
     * @param string $filename
     *
     * @return ICalEvent[]
     *
     * @throws UnableToGetEventsException
     */
    public function getEvents(string $filename): array;
}
