<?php

namespace HDNET\Calendarize\Tests\Unit\Service;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Ical\VObjectEventAdapter;
use Sabre\VObject\Reader;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use HDNET\Calendarize\Service\EventConfigurationService;
use HDNET\Calendarize\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EventConfigurationServiceTest extends AbstractUnitTestCase
{
    public static function hydrateRecurringConfigurationDataProvider(): \Generator
    {
        yield 'empty RRULE' => [[], ['frequency' => ConfigurationInterface::FREQUENCY_NONE]];
        yield 'minutely frequency' => [
            ['FREQ' => 'MINUTELY'],
            ['frequency' => ConfigurationInterface::FREQUENCY_MINUTELY],
        ];
        yield 'hourly frequency' => [
            ['FREQ' => 'HOURLY'],
            ['frequency' => ConfigurationInterface::FREQUENCY_HOURLY],
        ];
        yield 'daily frequency' => [
            ['FREQ' => 'DAILY'],
            ['frequency' => ConfigurationInterface::FREQUENCY_DAILY],
        ];
        yield 'daily frequency limited to weekdays' => [
            ['FREQ' => 'DAILY', 'BYDAY' => 'MO,WE'],
            [
                'frequency' => ConfigurationInterface::FREQUENCY_DAILY,
                'day' => [ConfigurationInterface::DAY_MONDAY, ConfigurationInterface::DAY_WEDNESDAY],
            ],
        ];
        yield 'hourly frequency limited to a weekday (BYDAY as array)' => [
            ['FREQ' => 'HOURLY', 'BYDAY' => ['SA'], 'INTERVAL' => '2'],
            [
                'frequency' => ConfigurationInterface::FREQUENCY_HOURLY,
                'counterInterval' => 2,
                'day' => [ConfigurationInterface::DAY_SATURDAY],
            ],
        ];
        yield 'daily frequency ignores BYSETPOS' => [
            ['FREQ' => 'DAILY', 'BYDAY' => 'FR', 'BYSETPOS' => '2'],
            [
                'frequency' => ConfigurationInterface::FREQUENCY_DAILY,
                'day' => [ConfigurationInterface::DAY_FRIDAY],
            ],
        ];
        yield 'weekly frequency ignores BYDAY' => [
            ['FREQ' => 'WEEKLY', 'BYDAY' => 'MO'],
            ['frequency' => ConfigurationInterface::FREQUENCY_WEEKLY],
        ];
        yield 'weekly frequency with until' => [
            ['FREQ' => 'MONTHLY', 'UNTIL' => '20060402T070000Z'],
            ['frequency' => ConfigurationInterface::FREQUENCY_MONTHLY, 'tillDate' => '2006-04-02'],
        ];
        yield 'monthly frequency with count' => [
            ['FREQ' => 'MONTHLY', 'COUNT' => '3'],
            ['frequency' => ConfigurationInterface::FREQUENCY_MONTHLY, 'counterAmount' => 2],
        ];
        yield 'yearly frequency with interval' => [
            ['FREQ' => 'YEARLY', 'INTERVAL' => '4'],
            ['frequency' => ConfigurationInterface::FREQUENCY_YEARLY, 'counterInterval' => 4],
        ];
        yield 'monthly on the last Friday' => [
            ['FREQ' => 'MONTHLY', 'BYDAY' => '-1FR'],
            [
                'frequency' => ConfigurationInterface::FREQUENCY_MONTHLY,
                'recurrence' => ConfigurationInterface::RECURRENCE_LAST,
                'day' => [ConfigurationInterface::DAY_FRIDAY],
            ],
        ];
        yield 'third Tuesday, Wednesday, or Thursday into the month' => [
            ['FREQ' => 'MONTHLY', 'BYDAY' => 'TU,WE,TH', 'BYSETPOS' => '3'],
            [
                'frequency' => ConfigurationInterface::FREQUENCY_MONTHLY,
                'recurrence' => ConfigurationInterface::RECURRENCE_THIRD,
                'day' => [ConfigurationInterface::DAY_TUESDAY, ConfigurationInterface::DAY_WEDNESDAY, ConfigurationInterface::DAY_THURSDAY],
            ],
        ];
        yield 'second-to-last weekday of the month (BYDAY as array)' => [
            ['FREQ' => 'MONTHLY', 'BYDAY' => ['MO', 'TU', 'WE', 'TH', 'FR'], 'BYSETPOS' => '-2'],
            [
                'frequency' => ConfigurationInterface::FREQUENCY_MONTHLY,
                'recurrence' => ConfigurationInterface::RECURRENCE_NEXT_TO_LAST,
                'day' => [
                    ConfigurationInterface::DAY_MONDAY,
                    ConfigurationInterface::DAY_TUESDAY,
                    ConfigurationInterface::DAY_WEDNESDAY,
                    ConfigurationInterface::DAY_THURSDAY,
                    ConfigurationInterface::DAY_FRIDAY,
                ],
            ],
        ];
        yield 'every second week' => [
            ['FREQ' => 'WEEKLY', 'INTERVAL' => '2'],
            ['frequency' => ConfigurationInterface::FREQUENCY_WEEKLY, 'counterInterval' => 2],
        ];
    }

    #[AllowMockObjectsWithoutExpectations]
    #[DataProvider(methodName: 'hydrateRecurringConfigurationDataProvider')]
    public function testHydrateRecurringConfiguration(array $rrule, array $expected): void
    {
        $subject = $this->getAccessibleMock(EventConfigurationService::class, null);
        $logger = $this->createMock(NullLogger::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::never())->method('warning');
        $subject->setLogger($logger);

        $row = $subject->_call('mapRruleToConfiguration', $rrule);

        if (isset($row['day'])) {
            $row['day'] = GeneralUtility::trimExplode(',', $row['day'], true);
        }
        if (isset($row['tillDate']) && $row['tillDate'] instanceof \DateTimeInterface) {
            $row['tillDate'] = $row['tillDate']->format('Y-m-d');
        }

        self::assertEqualsCanonicalizing($expected, $row);
    }

    public function testHydrateCalendarizeAddsAndCleansUpExclusions(): void
    {
        $timezone = @date_default_timezone_get();
        date_default_timezone_set('UTC');

        $ics = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//CalendarizeTest
BEGIN:VEVENT
UID:series@example.com
DTSTART:20240101T100000Z
DTEND:20240101T110000Z
RRULE:FREQ=WEEKLY;COUNT=5
%s
END:VEVENT
END:VCALENDAR
';
        $subject = new EventConfigurationService();
        $subject->setLogger(new NullLogger());
        $calendarize = new ObjectStorage();

        try {
            $event = new VObjectEventAdapter(Reader::read(\sprintf($ics, 'EXDATE:20240108T100000Z,20240115T100000Z'))->VEVENT);
            $subject->hydrateCalendarize($calendarize, $event, 'series', 1);

            $configurations = iterator_to_array($calendarize, false);
            self::assertCount(3, $configurations);
            /** @var Configuration $exclude */
            $exclude = $configurations[1];
            self::assertSame(ConfigurationInterface::HANDLING_EXCLUDE, $exclude->getHandling());
            self::assertSame('2024-01-08', $exclude->getStartDate()->format('Y-m-d'));
            self::assertSame(36000, $exclude->getStartTime());
            self::assertSame(ConfigurationInterface::FREQUENCY_NONE, $exclude->getFrequency());

            // Re-import without the first EXDATE removes the outdated exclusion
            $event = new VObjectEventAdapter(Reader::read(\sprintf($ics, 'EXDATE:20240115T100000Z'))->VEVENT);
            $subject->hydrateCalendarize($calendarize, $event, 'series', 1);

            $importIds = array_map(
                static fn(Configuration $configuration) => $configuration->getImportId(),
                iterator_to_array($calendarize, false),
            );
            self::assertSame(['series', 'series-exclude-20240115T36000'], $importIds);
        } finally {
            date_default_timezone_set($timezone);
        }
    }
}
