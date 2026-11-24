<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Unit\Ical;

use HDNET\Calendarize\Ical\ICalEvent;
use HDNET\Calendarize\Ical\VObjectEventAdapter;
use Sabre\VObject\Reader;

class VObjectEventAdapterTest extends ICalEventTestCase
{
    protected function getEvent(string $content): ICalEvent
    {
        $vcalendar = Reader::read($content);

        /* @var \Sabre\VObject\Component\VEvent $vevent */
        $vevent = $vcalendar->VEVENT;

        return new VObjectEventAdapter($vevent);
    }

    public function testRecurrenceIdIsPartOfUidAndExcludedInMainEvent(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:series@example.com
SUMMARY:Main
DTSTART:20240101T100000Z
DTEND:20240101T110000Z
RRULE:FREQ=WEEKLY;COUNT=5
EXDATE:20240108T100000Z
END:VEVENT
BEGIN:VEVENT
UID:series@example.com
SUMMARY:Moved
RECURRENCE-ID:20240115T100000Z
DTSTART:20240116T120000Z
DTEND:20240116T130000Z
END:VEVENT
BEGIN:VEVENT
UID:other@example.com
SUMMARY:Other
RECURRENCE-ID:20240122T100000Z
DTSTART:20240122T100000Z
END:VEVENT
END:VCALENDAR
';
        $events = iterator_to_array(Reader::read($input)->select('VEVENT'), false);
        $main = new VObjectEventAdapter($events[0]);
        $moved = new VObjectEventAdapter($events[1]);

        self::assertSame('series@example.com', $main->getUid());
        self::assertSame('series@example.com-20240115T100000Z', $moved->getUid());
        self::assertSame([], $moved->getExcludedDates());

        $excluded = array_map(
            static fn(\DateTimeInterface $date): string => $date->format('Y-m-d H:i'),
            $main->getExcludedDates(),
        );
        self::assertSame(['2024-01-08 10:00', '2024-01-15 10:00'], $excluded);
    }

    public function testNoExcludedDatesWithoutRRule(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:single@example.com
DTSTART:20240101T100000Z
EXDATE:20240108T100000Z
END:VEVENT
END:VCALENDAR
';
        self::assertSame([], (new VObjectEventAdapter(Reader::read($input)->VEVENT))->getExcludedDates());
    }
}
