<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\Tca;

use HDNET\Calendarize\Domain\Repository\RawIndexRepository;
use HDNET\Calendarize\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendPreviewTest extends AbstractFunctionalTestCase
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

        $this->importCSVDataSet(__DIR__ . '/Fixtures/tx_calendarize_domain_model_index.csv');
        $this->rawIndexRepository = GeneralUtility::makeInstance(RawIndexRepository::class);
    }

    public function testOutputOfLiveEvents(): void
    {
        $resultLive = $this->rawIndexRepository->findNextEvents('tx_calendarize_domain_model_event', 1, 5, 0);
        $resultWorkspace = $this->rawIndexRepository->findNextEvents('tx_calendarize_domain_model_event', 1, 5, 91);

        self::assertCount(2, $resultLive, 'Live count');
        self::assertEquals('2026-04-01', $resultLive[0]['start_date'], 'Live start_date 1');
        self::assertEquals('2026-04-29', $resultLive[1]['start_date'], 'Live start_date 2');

        self::assertCount(3, $resultWorkspace, 'Workspace count');
        self::assertEquals('2026-04-13', $resultWorkspace[0]['start_date'], 'Workspace start_date 1');
        self::assertEquals('2026-04-16', $resultWorkspace[1]['start_date'], 'Workspace start_date 2');
        self::assertEquals('2026-04-29', $resultWorkspace[2]['start_date'], 'Workspace start_date 3');
    }
}
