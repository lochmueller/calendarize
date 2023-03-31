<?php

namespace HDNET\Calendarize\Tests\Unit\Service;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Service\EventConfigurationService;
use HDNET\Calendarize\Tests\Unit\AbstractUnitTest;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EventConfigurationServiceTest extends AbstractUnitTest
{
    public function hydrateRecurringConfigurationDataProvider(): \Generator
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

    /**
     * @dataProvider hydrateRecurringConfigurationDataProvider
     *
     * @param array $rrule
     * @param array $expected
     */
    public function testHydrateRecurringConfiguration(array $rrule, array $expected): void
    {
        $subject = $this->getAccessibleMock(EventConfigurationService::class, ['dummy']);
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
}
