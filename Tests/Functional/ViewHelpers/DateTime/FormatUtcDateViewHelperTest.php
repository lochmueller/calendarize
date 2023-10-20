<?php

namespace HDNET\Calendarize\Tests\Functional\ViewHelpers\DateTime;

use HDNET\Calendarize\Tests\Functional\ViewHelpers\AbstractViewHelperTestCase;
use TYPO3\CMS\Fluid\View\StandaloneView;

class FormatUtcDateViewHelperTest extends AbstractViewHelperTestCase
{
    /**
     * @var string Backup of current timezone, it is manipulated in tests
     */
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

    /**
     * @dataProvider dateIsUtcTimezoneDataProvider
     *
     * @param \DateTimeInterface|string $date
     * @param string                    $expected
     */
    public function testDateIsUtcTimezone(\DateTimeInterface|string $date, string $expected): void
    {
        date_default_timezone_set('Europe/Moscow');
        $view = new StandaloneView();
        $template = '{namespace c=HDNET\Calendarize\ViewHelpers}' .
            '<c:dateTime.formatUtcDate date="{date}" format="Ymd\THis\Z" />';

        $view->setTemplateSource($template);
        $view->assign('date', $date);

        self::assertEquals($expected, $view->render());
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
