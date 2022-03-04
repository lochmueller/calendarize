<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\Indexing;

use HDNET\Calendarize\Domain\Repository\RawIndexRepository;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Tests\Functional\AbstractFunctionalTest;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FullReindexTest extends AbstractFunctionalTest
{
    /**
     * @var RawIndexRepository
     */
    protected $rawIndexRepository;

    /**
     * Sets up this test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/calendarize/Tests/Functional/Indexing/Fixtures/tx_calendarize_domain_model_configuration.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/calendarize/Tests/Functional/Indexing/Fixtures/tx_calendarize_domain_model_event.xml');
        $this->rawIndexRepository = GeneralUtility::makeInstance(RawIndexRepository::class);
    }

    public function testFindLiveIndexAfterIndexAVersion()
    {
        self::assertCount(0, $this->fetchAllIndex());

        /** @var IndexerService $indexer */
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindex('Event', 'tx_calendarize_domain_model_event', 2);

        $all = $this->fetchAllIndex();

        $live = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 1, 0);
        $workspace = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 1, 91);

        self::assertCount(3, $all, 'All count');

        self::assertCount(1, $live, 'Live count');
        self::assertCount(1, $workspace, 'Workspace count');

        self::assertEquals('2026-04-01', $live[0]['start_date'], 'Live date');
        self::assertEquals('2026-04-13', $workspace[0]['start_date'], 'Workspace date');
    }

    public function testFindIndexRecordsAfterReindexing()
    {
        self::assertCount(0, $this->fetchAllIndex());

        // Run reindex process
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();

        $live1 = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 1, 0);
        $workspace1 = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 1, 91);

        self::assertCount(1, $live1, 'Live 1 count');
        self::assertCount(1, $workspace1, 'Workspace 1 count');

        $live3 = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 3, 0);
        $workspace3 = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 3, 91);

        self::assertCount(0, $live3, 'Live 3 count');
        // @todo Check
        // self::assertCount(1, $workspace3, 'Workspace 3 count');

        $live4 = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 4, 0);
        $workspace4 = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 4, 91);

        self::assertCount(1, $live4, 'Live 4 count');
        self::assertCount(1, $workspace4, 'Workspace 4 count');

        $live5 = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 5, 0);
        $workspace5 = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 5, 91);

        self::assertCount(1, $live5, 'Live 5 count');
        self::assertCount(1, $workspace5, 'Workspace 5 count');

        $live6 = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 6, 0);
        $workspace6 = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 6, 91);

        self::assertCount(1, $live6, 'Live 6 count');
        // @todo Check
        // self::assertCount(0, $workspace6, 'Workspace 6 count');
    }

    protected function fetchAllIndex(): array
    {
        $q = HelperUtility::getDatabaseConnection('tx_calendarize_domain_model_index')->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll();

        return $q->select('uid', 'foreign_table', 'foreign_uid', 'start_date', 't3ver_wsid', 't3ver_state')
            ->from('tx_calendarize_domain_model_index')
            ->execute()->fetchAll();
    }
}
