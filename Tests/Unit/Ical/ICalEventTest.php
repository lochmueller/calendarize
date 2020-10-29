<?php

declare(strict_types=1);
namespace HDNET\Calendarize\Tests\Unit\Ical;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Ical\ICalEvent;
use HDNET\Calendarize\Tests\Unit\AbstractUnitTest;

/** @coversDefaultClass ICalEvent */
abstract class ICalEventTest extends AbstractUnitTest
{

    /**
     * @return ICalEvent
     */
    abstract protected function getEvent(string $content): ICalEvent;

    //-----------------------------------------------------
    // Tests
    //-----------------------------------------------------

    public function testMinimalEmpty()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:8f0c4ad6-c374-4411-af02-baa92a5522f3@example.com
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);
        self::assertEquals('8f0c4ad6-c374-4411-af02-baa92a5522f3@example.com', $event->getUid());
        self::assertNull($event->getTitle());
        self::assertNull($event->getDescription());
        self::assertNull($event->getLocation());
        self::assertNull($event->getOrganizer());
        self::assertNull($event->getStartDate());
        self::assertEquals(ICalEvent::ALLDAY_START_TIME, $event->getStartTime());
        self::assertEquals(ICalEvent::ALLDAY_END_TIME, $event->getEndTime());
        self::assertTrue($event->isAllDay());
        self::assertEquals(ConfigurationInterface::STATE_DEFAULT, $event->getState());
    }
    public function testAllGetters()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:8a6b0722-7bd4-4215-8be1-a38add5997f2@example.com
DTSTAMP:19970901T130000Z
DTSTART:20180902T083000Z
DTEND:20180907T112530Z
SUMMARY:Anniversary\, snacks and more
DESCRIPTION:Anniversary of Steve and Julia
LOCATION:659 Hiney Road\, Las Vegas
ORGANIZER;CN=John Smith:MAILTO:john.smith@example.com
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);
        self::assertEquals('8a6b0722-7bd4-4215-8be1-a38add5997f2@example.com', $event->getUid());
        self::assertEquals('Anniversary, snacks and more', $event->getTitle());
        self::assertEquals('Anniversary of Steve and Julia', $event->getDescription());
        self::assertEquals('20180902', $event->getStartDate()->format('Ymd'));
        self::assertEquals('20180907', $event->getEndDate()->format('Ymd'));
        self::assertEquals(30600, $event->getStartTime());
        self::assertEquals(41130, $event->getEndTime());
        self::assertEquals('659 Hiney Road, Las Vegas', $event->getLocation());
        self::assertStringContainsStringIgnoringCase('john', $event->getOrganizer());
        self::assertStringContainsStringIgnoringCase('smith', $event->getOrganizer());
        self::assertFalse($event->isAllDay());
        self::assertFalse($event->isOpenEndTime());
        self::assertEquals(ConfigurationInterface::STATE_DEFAULT, $event->getState());
    }

    /** @covers ICalEvent::getUid */
    public function testUid()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:0dec0ead-2517-4b1c-80bf-fcf0f1756fd7@example.com
DTSTART:20200916T150000Z
DTSTAMP:20201024T154452Z
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);
        self::assertEquals('0dec0ead-2517-4b1c-80bf-fcf0f1756fd7@example.com', $event->getUid());
    }

    /** @covers ICalEvent::getTitle */
    public function testTitle()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:aadbecf5-a842-4c42-891e-39c29fe86ac9@example.com
DTSTART:20200917T180000Z
DTSTAMP:20201024T154452Z
SUMMARY:Anniversary\, snacks and more
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);
        self::assertEquals('Anniversary, snacks and more', $event->getTitle());
    }

    /** @covers ICalEvent::getDescription */
    public function testDescription()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:aadbecf5-a842-4c42-891e-39c29fe86ac9@example.com
DTSTART:20200917T180000Z
DTSTAMP:20201024T154452Z
DESCRIPTION:Anniversary of Steve and Julia
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);
        self::assertEquals('Anniversary of Steve and Julia', $event->getDescription());
    }

    /** @covers ICalEvent::getLocation */
    public function testLocation()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:8a6b0722-7bd4-4215-8be1-a38add5997f2@example.com
DTSTAMP:19970901T130000Z
DTSTART:20180902T083000Z
LOCATION:Platz der Republik 1\, 11011 Berlin
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);
        self::assertEquals('Platz der Republik 1, 11011 Berlin', $event->getLocation());
    }

    /** @covers ICalEvent::getOrganizer */
    public function testOrganizer()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:45378646-32e5-4669-8598-f2a70bfe957c@example.com
DTSTAMP:19970901T130000Z
DTSTART:20180902T083000Z
ORGANIZER;CN=John Smith:MAILTO:john.smith@example.com
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);
        self::assertStringContainsStringIgnoringCase('john', $event->getOrganizer());
        self::assertStringContainsStringIgnoringCase('smith', $event->getOrganizer());
    }

    /** @covers ICalEvent::getState */
    public function testStateCanceled()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:25048f68-6ca5-4c1d-ad00-d80fd2c80d67@example.com
DTSTAMP:20201023T205354Z
DTSTART;VALUE=DATE:20201101
STATUS:CANCELLED
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);
        self::assertEquals(ConfigurationInterface::STATE_CANCELED, $event->getState());
    }

    public function testDateAndTime()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:ff42579b-31e4-472e-86f2-e0798b79055f@example.com
DTSTAMP:20180907T083000Z
DTSTART:20180907T083000Z
DTEND:20180907T093000Z
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);

        self::assertEquals('20180907', $event->getStartDate()->format('Ymd'));
        self::assertEquals('20180907', $event->getEndDate()->format('Ymd'));
        self::assertEquals(30600, $event->getStartTime());
        self::assertEquals(34200, $event->getEndTime());
        self::assertFalse($event->isAllDay());
    }

    public function testAllDayMultipleDays()
    {
        /* RFC 5545 Section 3.6.1 (Page 55)
         * The following is an example of the "VEVENT" calendar component
         * used to represent a multi-day event scheduled from June 28th, 2007
         * to July 8th, 2007 inclusively.  Note that the "DTEND" property is
         * set to July 9th, 2007, since the "DTEND" property specifies the
         * non-inclusive end of the event.
         */

        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:20070423T123432Z-541111@example.com
DTSTAMP:20070423T123432Z
DTSTART;VALUE=DATE:20070628
DTEND;VALUE=DATE:20070709
SUMMARY:Festival International de Jazz de Montreal
TRANSP:TRANSPARENT
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);

        self::assertEquals('20070628', $event->getStartDate()->format('Ymd'));
        self::assertEquals('20070708', $event->getEndDate()->format('Ymd'));
        self::assertEquals(ICalEvent::ALLDAY_START_TIME, $event->getStartTime());
        self::assertEquals(ICalEvent::ALLDAY_END_TIME, $event->getEndTime());
        self::assertTrue($event->isAllDay());
    }

    public function testAllDayOneDay()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:0a57ebc1-8b86-4f9a-90ca-42531560dad6@example.com
DTSTAMP:20201023T215502Z
DTSTART;VALUE=DATE:20150615
DTEND;VALUE=DATE:20150616
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);

        self::assertEquals('20150615', $event->getStartDate()->format('Ymd'));
        self::assertEquals('20150615', $event->getEndDate()->format('Ymd'));
        self::assertEquals(ICalEvent::ALLDAY_START_TIME, $event->getStartTime());
        self::assertEquals(ICalEvent::ALLDAY_END_TIME, $event->getEndTime());
        self::assertTrue($event->isAllDay());
    }

    public function testAllDayNoDtEnd()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:c6d71802-a13a-4cda-b32f-75c36fb0e162@example.com
DTSTAMP:20201023T215502Z
DTSTART;VALUE=DATE:20400323
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);

        self::assertEquals('20400323', $event->getStartDate()->format('Ymd'));
        self::assertNull($event->getEndDate());
        self::assertTrue($event->isAllDay());
        self::assertEquals(ICalEvent::ALLDAY_START_TIME, $event->getStartTime());
        self::assertEquals(ICalEvent::ALLDAY_END_TIME, $event->getEndTime());
    }
    public function testDuration()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:fca056d8-f0c4-4af6-91cd-a745b93db8b0@example.com
DTSTAMP:20180614T114000Z
DTSTART:20180801T181400Z
DURATION:P1DT1H
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);

        self::assertEquals('20180801', $event->getStartDate()->format('Ymd'));
        self::assertEquals('20180802', $event->getEndDate()->format('Ymd'));
        self::assertEquals(65640, $event->getStartTime());
        self::assertEquals(69240, $event->getEndTime());
        self::assertFalse($event->isAllDay());
    }
}
