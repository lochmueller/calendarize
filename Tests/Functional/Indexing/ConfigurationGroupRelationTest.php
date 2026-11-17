<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\Indexing;

use HDNET\Calendarize\Service\ConfigurationRelationService;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Tests\Functional\AbstractFunctionalTestCase;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class ConfigurationGroupRelationTest extends AbstractFunctionalTestCase
{
    protected ConfigurationRelationService $relationService;

    protected function setUp(): void
    {
        parent::setUp();

        $fixtures = __DIR__ . '/Fixtures/ConfigurationGroup/';
        $this->importCSVDataSet($fixtures . 'tx_calendarize_domain_model_configuration.csv');
        $this->importCSVDataSet($fixtures . 'tx_calendarize_domain_model_configurationgroup.csv');
        $this->importCSVDataSet($fixtures . 'tx_calendarize_domain_model_event.csv');

        $this->relationService = GeneralUtility::makeInstance(ConfigurationRelationService::class);
    }

    public function testFindEventsOfAChangedGroup(): void
    {
        $records = $this->relationService->findAffectedRecords([], [1]);

        self::assertEqualsCanonicalizing(
            [
                // Direct usage of the group 1 (incl. the translation of the event)
                'tx_calendarize_domain_model_event:1',
                'tx_calendarize_domain_model_event:2',
                // Nested group (3), that uses the group 1
                'tx_calendarize_domain_model_configurationgroup:3',
                'tx_calendarize_domain_model_event:3',
            ],
            $this->buildIdentifier($records),
        );
    }

    public function testFindEventsOfAChangedConfigurationOfAGroup(): void
    {
        // The configuration 1 is a configuration of the group 1
        $records = $this->relationService->findAffectedRecords([1], []);

        self::assertEqualsCanonicalizing(
            [
                'tx_calendarize_domain_model_configurationgroup:1',
                'tx_calendarize_domain_model_event:1',
                'tx_calendarize_domain_model_event:2',
                'tx_calendarize_domain_model_configurationgroup:3',
                'tx_calendarize_domain_model_event:3',
            ],
            $this->buildIdentifier($records),
        );
    }

    public function testUnrelatedGroupDoesNotAffectOtherEvents(): void
    {
        $records = $this->relationService->findAffectedRecords([], [4]);

        self::assertEqualsCanonicalizing(
            ['tx_calendarize_domain_model_event:4'],
            $this->buildIdentifier($records),
        );
    }

    public function testUnusedGroupHasNoRelations(): void
    {
        self::assertSame([], $this->relationService->findAffectedRecords([], [99]));
        self::assertSame([], $this->relationService->findAffectedRecords([], []));
    }

    public function testEventIndexIsUpdatedWhenAConfigurationOfTheGroupChanges(): void
    {
        /** @var IndexerService $indexer */
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();

        self::assertEqualsCanonicalizing(
            ['2026-04-01', '2026-04-02'],
            $this->fetchIndexDates('tx_calendarize_domain_model_event', 1),
        );

        // The initial indexing above loaded the configurations into the Extbase persistence session.
        // A real request creates the index after the DataHandler operations, so the state is cleared here.
        GeneralUtility::makeInstance(PersistenceManagerInterface::class)->clearState();

        // Change the date of a configuration of the group 1
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start(
            [
                'tx_calendarize_domain_model_configuration' => [
                    1 => ['start_date' => '2026-06-01'],
                ],
            ],
            [],
        );
        $dataHandler->process_datamap();
        self::assertSame([], $dataHandler->errorLog);

        self::assertEqualsCanonicalizing(
            ['2026-04-02', '2026-06-01'],
            $this->fetchIndexDates('tx_calendarize_domain_model_event', 1),
            'Index of the event with the group is updated',
        );
        self::assertEqualsCanonicalizing(
            // The nested group uses the group 1 and 2
            ['2026-04-02', '2026-05-01', '2026-06-01'],
            $this->fetchIndexDates('tx_calendarize_domain_model_event', 3),
            'Index of the event with the nested group is updated',
        );
    }

    protected function fetchIndexDates(string $tableName, int $uid): array
    {
        $queryBuilder = HelperUtility::getQueryBuilder(IndexerService::TABLE_NAME);
        $rows = $queryBuilder
            ->select('start_date')
            ->from(IndexerService::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('foreign_table', $queryBuilder->createNamedParameter($tableName)),
                $queryBuilder->expr()->eq('foreign_uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return array_column($rows, 'start_date');
    }

    protected function buildIdentifier(array $records): array
    {
        return array_map(static fn(array $record): string => $record['tableName'] . ':' . $record['uid'], $records);
    }
}
