<?php

declare(strict_types=1);

/**
 * DateTimeUtilityTest.
 */

namespace HDNET\Calendarize\Tests\Unit\DateTime;

use HDNET\Calendarize\Tests\Unit\AbstractUnitTest;
use HDNET\Calendarize\Utility\DateTimeUtility;

/**
 * DateTimeUtilityTest.
 */
class DateTimeUtilityTest extends AbstractUnitTest
{
    public function testDaySecondsOfDateTime()
    {
        $dateTime = new \DateTime('23.04.1987 04:36:34');
        $expected = 16594;

        self::assertEquals($expected, DateTimeUtility::getDaySecondsOfDateTime($dateTime), 'The seconds of the date do not match!');
    }

    public function testDateTimeForDbForExtbaseAreSame()
    {
        $oldTimezone = @date_default_timezone_get();
        $timezoneId = 'Europe/Moscow';

        date_default_timezone_set($timezoneId);

        $date = new \DateTime('2020-12-20 14:35:00');

        $dateDb = DateTimeUtility::fixDateTimeForDb(clone $date);
        $dateExt = DateTimeUtility::fixDateTimeForExtbase(clone $dateDb);

        date_default_timezone_set($oldTimezone);

        self::assertEquals($date, $dateExt);
        self::assertEquals($timezoneId, $dateExt->getTimezone()->getName(), 'Output date has local timezone.');
    }
}
