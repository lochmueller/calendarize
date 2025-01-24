<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\Indexing;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Domain\Repository\ConfigurationRepository;
use HDNET\Calendarize\Domain\Repository\RawIndexRepository;
use HDNET\Calendarize\Service\TimeTableService;
use HDNET\Calendarize\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TimeTableServiceTest extends AbstractFunctionalTestCase
{
    /**
     * @var RawIndexRepository
     */
    protected $rawIndexRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rawIndexRepository = GeneralUtility::makeInstance(RawIndexRepository::class);
    }

    /**
     * @dataProvider configurationProvider
     *
     * @group tmp
     */
    public function testIndexConfiguration(array $configurations, callable $assert)
    {
        $configurationRepository = $this->createMock(ConfigurationRepository::class);
        $configurationRepository->method('findByUid')->willReturnCallback(function ($id) use ($configurations) {
            return $configurations[$id] ?? null;
        });

        $timeTableService = new TimeTableService();
        $timeTableService->setConfigurationRepository($configurationRepository);

        $result = $timeTableService->getTimeTablesByConfigurationIds(array_keys($configurations), 0);
        self::assertNotEmpty($result, 'There should be index entries');
        $assert($result);
    }

    public static function configurationProvider(): \Generator
    {
        $new_configuration = static function (int $pid) {
            $configuration = new Configuration();
            $configuration->setPid($pid);

            return $configuration;
        };
        yield 'simple index Entry' => [
            [
                1 => $new_configuration(102)
                    ->setType(ConfigurationInterface::TYPE_TIME)
                    ->setStartDate(new \DateTime('2022-07-01'))
                    ->setStartTime(60 * 60 * 12)
                    ->setOpenEndTime(true)
                    ->setHandling(ConfigurationInterface::HANDLING_INCLUDE),
            ],
            function ($result) {},
        ];
        yield 'simple index Entry with hourly frequency' => [
            [
                1 => $new_configuration(102)
                    ->setType(ConfigurationInterface::TYPE_TIME)
                    ->setStartDate(new \DateTime('2022-07-01'))
                    ->setStartTime(60 * 60 * 12)
                    ->setEndTime(60 * 60 * 12 + (30 * 60))
                    ->setOpenEndTime(true)
                    ->setHandling(ConfigurationInterface::HANDLING_INCLUDE)
                    ->setFrequency(Configuration::FREQUENCY_HOURLY)
                    ->setCounterAmount(5),
            ],
            function ($result) {
                $items = array_values($result);

                self::assertCount(6, $items);
                self::assertEquals('12:00:00', BackendUtility::time($items[0]['start_time']));
                self::assertEquals('12:30:00', BackendUtility::time($items[0]['end_time']));
                self::assertEquals('13:00:00', BackendUtility::time($items[1]['start_time']));
                self::assertEquals('13:30:00', BackendUtility::time($items[1]['end_time']));
            },
        ];
    }
}
