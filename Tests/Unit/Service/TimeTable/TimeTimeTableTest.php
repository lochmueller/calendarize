<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Unit\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Service\TimeTable\TimeTimeTable;
use HDNET\Calendarize\Tests\Unit\AbstractUnitTestCase;
use HDNET\Calendarize\Utility\HelperUtility;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

class TimeTimeTableTest extends AbstractUnitTestCase
{
    protected bool $resetSingletonInstances = true;

    #[AllowMockObjectsWithoutExpectations]
    public function testDayFilterLimitsTheDailyFrequency(): void
    {
        $configuration = $this->createConfiguration(ConfigurationInterface::FREQUENCY_DAILY, '2024-01-01', 31);
        $configuration->setDay(ConfigurationInterface::DAY_MONDAY . ',' . ConfigurationInterface::DAY_WEDNESDAY);

        self::assertSame([
            '2024-01-01',
            '2024-01-03',
            '2024-01-08',
            '2024-01-10',
            '2024-01-15',
            '2024-01-17',
            '2024-01-22',
            '2024-01-24',
            '2024-01-29',
            '2024-01-31',
        ], $this->buildDates($configuration));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDayFilterRemovesTheNotMatchingBaseEntry(): void
    {
        // The 2024-01-02 is a tuesday, so the start date itself is filtered out
        $configuration = $this->createConfiguration(ConfigurationInterface::FREQUENCY_DAILY, '2024-01-02', 30);
        $configuration->setDay(ConfigurationInterface::DAY_MONDAY);

        self::assertSame([
            '2024-01-08',
            '2024-01-15',
            '2024-01-22',
            '2024-01-29',
        ], $this->buildDates($configuration));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDayFilterCountsOnlyTheCreatedDates(): void
    {
        $configuration = $this->createConfiguration(ConfigurationInterface::FREQUENCY_DAILY, '2024-01-01', 365);
        $configuration->setDay(ConfigurationInterface::DAY_MONDAY);
        $configuration->setCounterAmount(2);

        self::assertSame([
            '2024-01-01',
            '2024-01-08',
            '2024-01-15',
        ], $this->buildDates($configuration));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDayFilterRespectsTheInterval(): void
    {
        $configuration = $this->createConfiguration(ConfigurationInterface::FREQUENCY_DAILY, '2024-01-01', 31);
        $configuration->setDay(ConfigurationInterface::DAY_MONDAY);
        $configuration->setCounterInterval(2);

        // Every second day, but only the mondays are kept
        self::assertSame([
            '2024-01-01',
            '2024-01-15',
            '2024-01-29',
        ], $this->buildDates($configuration));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDayFilterIsIgnoredForTheWeeklyFrequency(): void
    {
        $configuration = $this->createConfiguration(ConfigurationInterface::FREQUENCY_WEEKLY, '2024-01-02', 29);
        $configuration->setDay(ConfigurationInterface::DAY_MONDAY);

        self::assertSame([
            '2024-01-02',
            '2024-01-09',
            '2024-01-16',
            '2024-01-23',
            '2024-01-30',
        ], $this->buildDates($configuration));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDayFilterStopsIfTheIncrementNeverMatches(): void
    {
        // Every seventh day always hits the same weekday, so a tuesday is never reached
        $configuration = $this->createConfiguration(ConfigurationInterface::FREQUENCY_DAILY, '2024-01-01', 365);
        $configuration->setDay(ConfigurationInterface::DAY_TUESDAY);
        $configuration->setCounterInterval(7);

        $dates = HelperUtility::silentFlashMessages(fn(): array => $this->buildDates($configuration));

        self::assertSame([], $dates);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testWithoutDayFilterAllDatesAreCreated(): void
    {
        $configuration = $this->createConfiguration(ConfigurationInterface::FREQUENCY_DAILY, '2024-01-01', 5);

        self::assertSame([
            '2024-01-01',
            '2024-01-02',
            '2024-01-03',
            '2024-01-04',
            '2024-01-05',
        ], $this->buildDates($configuration));
    }

    protected function createConfiguration(string $frequency, string $startDate, int $tillDays): Configuration
    {
        $configuration = new Configuration();
        $configuration->setType(ConfigurationInterface::TYPE_TIME);
        $configuration->setFrequency($frequency);
        $configuration->setStartDate(new \DateTime($startDate));
        // Prevent the usage of the extension configuration
        $configuration->setTillDays($tillDays);
        $configuration->setTillDaysPast(0);

        return $configuration;
    }

    /**
     * @return array<string> the start dates of the generated time table
     */
    protected function buildDates(Configuration $configuration): array
    {
        $subject = $this->getAccessibleMock(TimeTimeTable::class, ['getFrequencyLimitPerItem']);
        $subject->method('getFrequencyLimitPerItem')->willReturn(300);

        $times = [];
        $subject->handleConfiguration($times, $configuration);

        $dates = array_map(
            static fn(array $entry): string => $entry['start_date']->format('Y-m-d'),
            array_values($times),
        );
        sort($dates);

        return $dates;
    }
}
