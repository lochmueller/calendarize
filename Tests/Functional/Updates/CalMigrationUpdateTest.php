<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\Updates;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationGroup;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Domain\Repository\RawIndexRepository;
use HDNET\Calendarize\Updates\CalMigrationUpdate;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/*
 * @requires PHP < 8.0.0
 */
class CalMigrationUpdateTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/autoloader',
        'typo3conf/ext/calendarize',
        'typo3conf/ext/calendarize/Tests/Functional/Fixtures/Extensions/cal',
    ];

    /**
     * @var CalMigrationUpdate
     */
    protected $subject;

    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @var RawIndexRepository
     */
    protected $rawIndexRepository;

    protected function setUp(): void
    {
        if (\PHP_VERSION_ID >= 80000 || (new Typo3Version())->getMajorVersion() > 10) {
            self::markTestSkipped('TYPO3/PHP version not supported!');
        }
        parent::setUp();

        $this->subject = new CalMigrationUpdate();
        $this->subject->setLogger(new NullLogger());
        $this->subject->setOutput($this->getMockBuilder(OutputInterface::class)->getMock());

        $this->eventRepository = GeneralUtility::makeInstance(EventRepository::class);
        $this->rawIndexRepository = GeneralUtility::makeInstance(RawIndexRepository::class);
    }

    public function testCheckForUpdateNoEvents(): void
    {
        $description = '';
        $result = $this->subject->checkForUpdate($description);
        self::assertFalse($result);
    }

    public function testCheckForUpdateEvents(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/cal.csv');

        $description = '';
        $result = $this->subject->checkForUpdate($description);
        self::assertTrue($result);
    }

    public function testFullMigration(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/cal.csv');

        $successful = $this->subject->executeUpdate();

        self::assertTrue($successful);

        // Clear state, since the categories where added after the events where loaded into the in-memory state
        GeneralUtility::makeInstance(PersistenceManager::class)->clearState();

        /** @var Event $event */
        $event = $this->eventRepository->findOneByImportId('calMigration:74');

        self::assertNotNull($event);
        self::assertEquals('EventTitle', $event->getTitle());
        self::assertEquals('Tease me!', $event->getAbstract());
        self::assertEquals('Lorem ipsum', $event->getDescription());
        self::assertStringContainsString('Brandenburger Tor', $event->getLocation());

        /** @var Configuration[] $calendarize */
        $calendarize = $event->getCalendarize()->toArray();
        self::assertNotEmpty($calendarize);

        self::assertEquals('20120119', $calendarize[0]->getStartDate()->format('Ymd'));
        // calendarize does not exclude the first occurrence, whereas cal (and ics) include them
        self::assertEquals(50 - 1, $calendarize[0]->getCounterAmount());
        self::assertEquals(ConfigurationInterface::FREQUENCY_WEEKLY, $calendarize[0]->getFrequency());
        self::assertEquals(ConfigurationInterface::DAY_THURSDAY, $calendarize[0]->getDay());

        // Check (a) category migration and (b) linkage between event and category
        $categories = $event->getCategories()->toArray();
        self::assertNotEmpty($categories);
        self::assertEquals('Food', $categories[0]->getTitle());
    }

    public function testExceptionGroups(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/cal-exception-groups.csv');

        $successful = $this->subject->executeUpdate();

        self::assertTrue($successful);

        /** @var Event $event */
        $event = $this->eventRepository->findOneByImportId('calMigration:10');

        self::assertNotNull($event);
        // Check correct amount of indices = 10 - 1 - 2 = 7
        self::assertEquals(7, $this->rawIndexRepository->countAllEvents('tx_calendarize_domain_model_event', $event->getUid()));

        /** @var Configuration[] $calendarize */
        $calendarize = $event->getCalendarize()->toArray();
        self::assertCount(2, $calendarize);

        // We expect an exclude configuration with two groups, each with one configuration
        $calendarizeGroup = current(array_filter($calendarize, static function ($v) {return ConfigurationInterface::TYPE_GROUP === $v->getType(); }));
        self::assertEquals(ConfigurationInterface::HANDLING_EXCLUDE, $calendarizeGroup->getHandling());
        /** @var ConfigurationGroup[] $groups */
        $groups = $calendarizeGroup->getGroups()->toArray();
        self::assertCount(1, $groups[0]->getConfigurationIds());
        self::assertCount(1, $groups[1]->getConfigurationIds());
    }

    public function testExceptionSingle()
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/cal-exception-single.csv');

        $successful = $this->subject->executeUpdate();

        self::assertTrue($successful);

        /** @var Event $event */
        $event = $this->eventRepository->findOneByImportId('calMigration:12');

        self::assertNotNull($event);

        /** @var Configuration[] $calendarize */
        $calendarize = $event->getCalendarize()->toArray();
        self::assertCount(2, $calendarize);

        // Check correct amount of indices = 3 - 1 = 2
        self::assertEquals(2, $this->rawIndexRepository->countAllEvents('tx_calendarize_domain_model_event', $event->getUid()));
    }

    // Translated l10n/l18n events
    // Images
}
