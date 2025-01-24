<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\EventListener;

use HDNET\Calendarize\Domain\Model\PluginConfiguration;
use HDNET\Calendarize\Event\IndexRepositoryDefaultConstraintEvent;
use HDNET\Calendarize\EventListener\CategoryConstraintEventListener;
use HDNET\Calendarize\Register;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class CategoryConstraintEventListenerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/calendarize'];

    protected CategoryConstraintEventListener $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventsWithCategories.csv');
        $this->subject = new CategoryConstraintEventListener();
    }

    public function testPluginConfigurationWithSingleCategory(): void
    {
        $categoryMock = $this->getMockBuilder(Category::class)->getMock();
        $categoryMock->method('getUid')->willReturn(30);
        $categories = new ObjectStorage();
        $categories->attach($categoryMock);
        $pluginConfiguration = new PluginConfiguration();
        $pluginConfiguration->setCategories($categories);

        $event = new IndexRepositoryDefaultConstraintEvent(
            [],
            [Register::UNIQUE_REGISTER_KEY],
            [
                'settings' => [
                    'categoryConjunction' => 'or',
                    'pluginConfiguration' => $pluginConfiguration,
                ],
            ],
        );
        ($this->subject)($event);

        self::assertEquals(['tx_calendarize_domain_model_event' => [11]], $event->getForeignIds());
    }

    public function testPluginConfigurationAndContentRecord(): void
    {
        // This tests for a bug where both plugin configuration and content record are set and a category id is double
        $categoryMock = $this->getMockBuilder(Category::class)->getMock();
        $categoryMock->method('getUid')->willReturn(31);
        $categories = new ObjectStorage();
        $categories->attach($categoryMock);
        $pluginConfiguration = new PluginConfiguration();
        $pluginConfiguration->setCategories($categories);

        $event = new IndexRepositoryDefaultConstraintEvent(
            [],
            [Register::UNIQUE_REGISTER_KEY],
            [
                'contentRecord' => ['uid' => 22],
                'settings' => [
                    'categoryConjunction' => 'and',
                    'pluginConfiguration' => $pluginConfiguration,
                ],
            ],
        );
        ($this->subject)($event);
        // The resulting category selection should be B & C (31 & 32)
        self::assertEquals(['tx_calendarize_domain_model_event' => [13]], $event->getForeignIds());
    }

    public static function contentRecordDataProvider(): array
    {
        return [
            'OR one category' => [
                20,
                'or',
                ['tx_calendarize_domain_model_event' => [11]],
            ],
            'OR two categories' => [
                22,
                'or',
                ['tx_calendarize_domain_model_event' => [12, 13]],
            ],
            'EMPTY conjunction (legacy, interpreted as OR)' => [
                22,
                '',
                ['tx_calendarize_domain_model_event' => [12, 13]],
            ],
            'AND one category' => [
                20,
                'and',
                ['tx_calendarize_domain_model_event' => [11]],
            ],
            'AND two categories' => [
                22,
                'and',
                ['tx_calendarize_domain_model_event' => [13]],
            ],
            'AND categories no match' => [
                21,
                'and',
                ['tx_calendarize_domain_model_event' => [-1]],
            ],
            'ALL categories (without any filter)' => [
                21,
                'all',
                [],
            ],
        ];
    }

    /**
     * @dataProvider contentRecordDataProvider
     */
    public function testContentRecordEventCategories(int $contentRecord, string $conjunction, array $expectedIndexIds): void
    {
        $event = new IndexRepositoryDefaultConstraintEvent(
            [],
            [Register::UNIQUE_REGISTER_KEY],
            [
                'contentRecord' => ['uid' => $contentRecord],
                'settings' => ['categoryConjunction' => $conjunction],
            ],
        );
        ($this->subject)($event);

        self::assertEquals($expectedIndexIds, $event->getForeignIds());
    }

    public function testContentRecordWithoutIndexTypes(): void
    {
        $event = new IndexRepositoryDefaultConstraintEvent(
            [],
            [],
            [
                'contentRecord' => ['uid' => 20],
                'settings' => ['categoryConjunction' => 'or'],
            ],
        );
        ($this->subject)($event);

        self::assertEquals([], $event->getForeignIds());
    }

    public function testContentRecordInvalidIndexTypes(): void
    {
        $event = new IndexRepositoryDefaultConstraintEvent(
            [],
            ['RandomTypeThatDoesNotExists'],
            [
                'contentRecord' => ['uid' => 20],
                'settings' => ['categoryConjunction' => 'or'],
            ],
        );
        ($this->subject)($event);

        self::assertEquals([], $event->getForeignIds());
    }

    public function testContentRecordTwoIndexTypes(): void
    {
        $event = new IndexRepositoryDefaultConstraintEvent(
            [],
            ['RandomTypeThatDoesNotExists', Register::UNIQUE_REGISTER_KEY],
            [
                'contentRecord' => ['uid' => 20],
                'settings' => ['categoryConjunction' => 'or'],
            ],
        );
        ($this->subject)($event);

        self::assertEquals(['tx_calendarize_domain_model_event' => [11]], $event->getForeignIds());
    }
}
