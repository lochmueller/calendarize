<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Controller\CalendarController;
use HDNET\Calendarize\Domain\Repository\CategoryRepository;
use HDNET\Calendarize\Event\GenericActionAssignmentEvent;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Gets all used categories from the default Event and assigns it to extended.categories in fluid.
 * This is only active for search actions!
 */
class CategoryFilterEventListener
{
    protected string $itemTableName = 'tx_calendarize_domain_model_event';

    protected string $itemFieldName = 'categories';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly CategoryRepository $categoryRepository,
    ) {}

    public function __invoke(GenericActionAssignmentEvent $event): void
    {
        if (CalendarController::class !== $event->getClassName() || 'searchAction' !== $event->getFunctionName()) {
            return;
        }
        if (!\in_array($this->itemTableName, array_column($event->getVariables()['configurations'] ?? [], 'tableName'), true)) {
            return;
        }
        $variables = $event->getVariables();
        $variables['extended']['categories'] = array_merge(
            $variables['extended']['categories'] ?? [],
            $this->getCategories($this->itemTableName, $this->itemFieldName),
        );

        $event->setVariables($variables);
    }

    /**
     * Gets all used categories of the default Event (self::itemTableName).
     */
    protected function getCategories(string $tableName, string $fieldName): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $queryBuilder->distinct()
            ->select('uid_local')
            ->from('sys_category_record_mm')
            ->where(
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq(
                        'sys_category_record_mm.tablenames',
                        $queryBuilder->createNamedParameter($tableName, Connection::PARAM_STR),
                    ),
                    $queryBuilder->expr()->eq(
                        'sys_category_record_mm.fieldname',
                        $queryBuilder->createNamedParameter($fieldName, Connection::PARAM_STR),
                    ),
                ),
            );

        $categoryIds = $queryBuilder
            ->executeQuery()
            ->fetchFirstColumn();

        if (empty($categoryIds)) {
            return [];
        }

        return $this->categoryRepository->findByIds(
            $categoryIds,
            ['title' => QueryInterface::ORDER_ASCENDING],
        )->toArray();
    }
}
