<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\ViewHelpers\DateTime;

use HDNET\Calendarize\Tests\Functional\ViewHelpers\AbstractViewHelperTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FormatUtcDateViewHelperTest extends AbstractViewHelperTestCase
{
    protected string $timezone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->timezone = @date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
        parent::tearDown();
    }

    #[DataProvider('dateIsUtcTimezoneDataProvider')]
    public function testDateIsUtcTimezone(\DateTimeInterface|string $date, string $expected): void
    {
        date_default_timezone_set('Europe/Moscow');

        $template = '{namespace c=HDNET\Calendarize\ViewHelpers}' .
            '<c:dateTime.formatUtcDate date="{date}" format="Ymd\THis\Z" />';

        self::assertEquals($expected, $this->renderTemplate($template, ['date' => $date]));
    }

    public static function dateIsUtcTimezoneDataProvider(): \Generator
    {
        yield 'Bash UTC' => ['Sun Apr 30 03:01:39 UTC 2006', '20060430T030139Z'];
        yield 'Unix' => ['1607785200', '20201212T150000Z'];
        yield 'ISO 8601 / Atom' => ['2020-12-12T18:00:00+01:00', '20201212T170000Z'];
        yield 'DateTime' => [new \DateTime('2012-12-21T08:00:00', new \DateTimeZone('Asia/Shanghai')), '20121221T000000Z'];
        yield 'DateTimeImmutable' => [new \DateTimeImmutable('2016-07-02T13:01:33', new \DateTimeZone('Indian/Mayotte')), '20160702T100133Z'];
    }
}
