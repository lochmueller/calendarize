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

    public function testFindIndexRecordsAfterReindexing()
    {
        self::markTestSkipped('@todo');

        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();

        $q = HelperUtility::getDatabaseConnection('tx_calendarize_domain_model_index')->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll();

        $result = $q->select('foreign_table', 'foreign_uid', 'start_date', 't3ver_wsid', 't3ver_state')
            ->from('tx_calendarize_domain_model_index')
            ->execute()->fetchAll();

        //var_dump($result);

        self::assertCount(4, $result);

        //$firstLive = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 1, 0);
        //$firstWorkspace = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 1, 91);
        //$secondLive = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 3, 0);
        //$secondWorkspace = $this->rawIndexRepository->findAllEvents('tx_calendarize_domain_model_event', 3, 91);

        //var_dump($firstLive);
        //var_dump($firstWorkspace);
        //#var_dump($secondLive);
        //var_dump($secondWorkspace);

        self::assertEquals(1, $result[0]['foreign_uid']);
        self::assertEquals(1, $result[1]['foreign_uid']); // also WS point to live records

        self::assertEquals('2026-04-01', $result[0]['start_date']);

        // @todo check
        // $this->assertEquals('2026-04-13', $result[1]['start_date']);
    }
}
