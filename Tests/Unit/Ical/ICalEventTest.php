<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Unit\Ical;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Ical\ICalEvent;
use HDNET\Calendarize\Tests\Unit\AbstractUnitTest;
use HDNET\Calendarize\Utility\DateTimeUtility;

/** @coversDefaultClass ICalEvent */
abstract class ICalEventTest extends AbstractUnitTest
{
    /**
     * @var string Backup of current timezone, it is manipulated in tests
     */
    protected $timezone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->timezone = @date_default_timezone_get();
        date_default_timezone_set('UTC');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
        parent::tearDown();
    }

    /**
     * @return ICalEvent
     */
    abstract protected function getEvent(string $content): ICalEvent;

    // -----------------------------------------------------
    // Tests
    // -----------------------------------------------------

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

    public function testDateAndTimeTimzone()
    {
        $timezone = new \DateTimeZone('America/Los_Angeles');
        date_default_timezone_set($timezone->getName());
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:0ee38aeb-ef2d-4a06-b01d-8a21d2552e4b@example.com
DTSTAMP:20150907T083000Z
DTSTART:20120101T073000Z
DTEND:20120101T083000Z
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);

        $expectedStart = new \DateTime('20120101T073000Z');
        $expectedStart->setTimezone($timezone);
        $expectedEnd = new \DateTime('20120101T083000Z');
        $expectedEnd->setTimezone($timezone);

        self::assertEquals($expectedStart->format('Ymd'), $event->getStartDate()->format('Ymd'));
        self::assertEquals($expectedEnd->format('Ymd'), $event->getEndDate()->format('Ymd'));
        self::assertEquals(DateTimeUtility::getDaySecondsOfDateTime($expectedStart), $event->getStartTime());
        self::assertEquals(DateTimeUtility::getDaySecondsOfDateTime($expectedEnd), $event->getEndTime());
        self::assertFalse($event->isAllDay());
    }

    public function testUnknownTimezone()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:de3e9a87-69ec-43f8-9332-5abe7e04f3ca@example.com
DTSTAMP:20220107T220312Z
DTSTART;TZID="Unknown timezone":20220214T123000
DTEND;TZID="Unknown timezone":20220214T133000
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);
        self::assertEquals('20220214', $event->getStartDate()->format('Ymd'));
        self::assertEquals('20220214', $event->getEndDate()->format('Ymd'));
        // With an unknown timezone we expect the time to be UTC or the server time zone
        // (depends on the library used, in this case these are the same!)
        self::assertEquals((12 * 60 + 30) * 60, $event->getStartTime());
        self::assertEquals((13 * 60 + 30) * 60, $event->getEndTime());
        self::assertFalse($event->isAllDay());
    }

    public function testAllDayTimezone()
    {
        date_default_timezone_set('America/Los_Angeles');
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:3c670bf2-e81b-4b36-9e39-e84418d4112a@example.com
DTSTAMP:20201023T215502Z
DTSTART;VALUE=DATE:20050515
DTEND;VALUE=DATE:20050516
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);

        self::assertEquals('20050515', $event->getStartDate()->format('Ymd'));
        self::assertEquals('20050515', $event->getEndDate()->format('Ymd'));
        self::assertEquals(ICalEvent::ALLDAY_START_TIME, $event->getStartTime());
        self::assertEquals(ICalEvent::ALLDAY_END_TIME, $event->getEndTime());
        self::assertTrue($event->isAllDay());
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

    public function testRRule()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:64843636-21d6-44e3-8f8f-6174845e2342@example.com
DTSTAMP:20050404T112124Z
DTSTART:20050404T195534Z
RRULE:FREQ=DAILY;INTERVAL=1;COUNT=45
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);
        $rrule = $event->getRRule();

        self::assertArrayHasKey('FREQ', $rrule);
        self::assertEqualsIgnoringCase('DAILY', $rrule['FREQ']);

        self::assertArrayHasKey('INTERVAL', $rrule);
        self::assertEqualsIgnoringCase('1', $rrule['INTERVAL']);

        self::assertArrayHasKey('COUNT', $rrule);
        self::assertEqualsIgnoringCase('45', $rrule['COUNT']);
    }

    public function testRawData()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:9b2505f4-7a45-44f6-b4ba-e9181e176d35@example.com
DTSTAMP:20210528T170133Z
DTSTART:20210623T160000Z
URL:http://abc.com/pub/calendars/jsmith/mytime.ics
X-ALT-DESC;FMTTYPE=text/html:<html><body><p>A <b>BOLD</b> and <i>italic</i> story!</p></body></html>
END:VEVENT
END:VCALENDAR
';
        $event = $this->getEvent($input);
        $raw = $event->getRawData();

        self::assertArrayHasKey('URL', $raw);
        self::assertEqualsIgnoringCase('http://abc.com/pub/calendars/jsmith/mytime.ics', $raw['URL'][0]);

        self::assertArrayHasKey('X-ALT-DESC', $raw);
        self::assertStringContainsStringIgnoringCase('<html><body><p>A <b>BOLD</b> and <i>italic</i> story!</p></body></html>', $raw['X-ALT-DESC'][0]);
    }
}
