<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\Domain\Repository;

use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Service\IndexerService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class IndexRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/calendarize'];

    protected IndexerService $indexerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventsWithCategories.csv');

        $this->indexerService = GeneralUtility::makeInstance(IndexerService::class);
        $this->indexerService->reindexAll();
    }

    /**
     * @dataProvider findBySearchDataProvider
     */
    public function testFindBySearch(
        int $language,
        ?\DateTimeInterface $startDate,
        ?\DateTimeInterface $endDate,
        array $customSearch,
        int $limit,
        array $expectedEventIds,
    ): void {
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('language', new LanguageAspect($language, $language, LanguageAspect::OVERLAYS_ON));

        $subject = GeneralUtility::makeInstance(IndexRepository::class);
        $subject->setIndexTypes([Register::UNIQUE_REGISTER_KEY]);
        $subject->setOverridePageIds([2, 3]);

        $result = $subject->findBySearch($startDate, $endDate, $customSearch, $limit)->toArray();
        $eventIds = array_map(static fn($i) => $i->getForeignUid(), $result);

        self::assertEquals($expectedEventIds, $eventIds);
    }

    public static function findBySearchDataProvider(): array
    {
        return [
            'no filter' => [0, null, null, [], 0, [10, 20, 30]],
            'category' => [0, null, null, ['categories' => ['28']], 0, [20]],
            'category translated' => [1, null, null, ['categories' => ['28']], 0, [21]],
            'fullText' => [0, null, null, ['fullText' => 'Only'], 0, [20, 30]],
            'fullText translated' => [1, null, null, ['fullText' => 'No'], 0, [11]],
            'start and end date' => [0, new \DateTime('2025-02-28'), new \DateTime('2025-03-01'), [], 0, [10]],
            'dates + categories + text' => [
                0,
                new \DateTime('2025-02-28'),
                new \DateTime('2025-03-05'),
                ['categories' => ['28', '29'], 'fullText' => 'B'],
                0,
                [30],
            ],
        ];
    }
}
