<?php

namespace HDNET\Calendarize\Tests\ViewHelpers\DateTime;

use HDNET\Calendarize\ViewHelpers\DateTime\FormatUtcDateViewHelper;
use TYPO3\TestingFramework\Fluid\Unit\ViewHelpers\ViewHelperBaseTestcase;

class FormatUtcDateViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var string Backup of current timezone, it is manipulated in tests
     */
    protected $timezone;

    /**
     * @var FormatUtcDateViewHelper
     */
    protected $viewHelper;

    protected $resetSingletonInstances = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->timezone = @date_default_timezone_get();
        $this->viewHelper = new FormatUtcDateViewHelper();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
        parent::tearDown();
    }

    /**
     * @dataProvider dateIsUtcTimezoneDataProvider
     *
     * @param \DateTimeInterface|string|number $date
     * @param string                           $expected
     */
    public function testDateIsUtcTimezone($date, string $expected)
    {
        date_default_timezone_set('Europe/Moscow');
        $format = 'Ymd\THis\Z';
        $this->setArgumentsUnderTest(
            $this->viewHelper,
            [
                'date' => $date,
                'format' => $format,
            ]
        );
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        self::assertEquals($expected, $actualResult);
    }

    public function dateIsUtcTimezoneDataProvider(): \Generator
    {
        yield 'Bash UTC' => ['Sun Apr 30 03:01:39 UTC 2006', '20060430T030139Z'];
        yield 'Unix' => ['1607785200', '20201212T150000Z'];
        yield 'ISO 8601 / Atom' => ['2020-12-12T18:00:00+01:00', '20201212T170000Z'];
        yield 'DateTime' => [new \DateTime('2012-12-21T08:00:00', new \DateTimeZone('Asia/Shanghai')), '20121221T000000Z'];
        yield 'DateTimeImmutable' => [new \DateTimeImmutable('2016-07-02T13:01:33', new \DateTimeZone('Indian/Mayotte')), '20160702T100133Z'];
    }
}
