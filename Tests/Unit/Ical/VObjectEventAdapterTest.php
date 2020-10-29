<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Unit\Ical;

use HDNET\Calendarize\Ical\ICalEvent;
use HDNET\Calendarize\Ical\VObjectEventAdapter;
use Sabre\VObject\Reader;

class VObjectEventAdapterTest extends ICalEventTest
{
    protected function getEvent(string $content): ICalEvent
    {
        $vcalendar = Reader::read($content);

        /* @var \Sabre\VObject\Component\VEvent $vevent */
        $vevent = $vcalendar->VEVENT;

        return new VObjectEventAdapter($vevent);
    }
}
