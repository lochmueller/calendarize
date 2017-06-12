<?php

/**
 * DateTimeUtilityTest
 */
namespace HDNET\Calendarize\Tests\Unit\DateTime;

use HDNET\Calendarize\Tests\Unit\AbstractUnitTest;
use HDNET\Calendarize\Utility\DateTimeUtility;

/**
 * DateTimeUtilityTest
 */
class DateTimeUtilityTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function testDaySecondsOfDateTime()
    {
        $dateTime = new \DateTime('23.04.1987 04:36:34');
        $expected = 16594;

        $this->assertEquals($expected, DateTimeUtility::getDaySecondsOfDateTime($dateTime), 'The seconds of the date do not match!');
    }
}
