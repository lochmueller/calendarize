<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\Indexing;

use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Tests\Functional\AbstractFunctionalTest;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FullReindexTest extends AbstractFunctionalTest
{
    /**
     * Sets up this test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/calendarize/Tests/Functional/Indexing/Fixtures/tx_calendarize_domain_model_configuration.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/calendarize/Tests/Functional/Indexing/Fixtures/tx_calendarize_domain_model_event.xml');
    }

    public function testFindIndexRecordsAfterReindexing()
    {
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();

        $q = HelperUtility::getDatabaseConnection('tx_calendarize_domain_model_index')->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll();

        $result = $q->select('foreign_table', 'foreign_uid', 'start_date', 't3ver_wsid')
            ->from('tx_calendarize_domain_model_index')
            ->execute()->fetchAll();

        self::assertCount(2, $result);
        self::assertEquals(1, $result[0]['foreign_uid']);
        self::assertEquals(1, $result[1]['foreign_uid']); // also WS point to live records

        self::assertEquals('2026-04-01', $result[0]['start_date']);
        // @todo check
        // $this->assertEquals('2026-04-13', $result[1]['start_date']);
    }
}
