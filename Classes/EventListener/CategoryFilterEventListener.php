<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

use Doctrine\DBAL\Connection;
use HDNET\Calendarize\Controller\CalendarController;
use HDNET\Calendarize\Event\GenericActionAssignmentEvent;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Gets all used categories from the default Event and assigns it to extended.categories in fluid.
 * This is only active for search actions!
 */
class CategoryFilterEventListener
{
    protected string $itemTableName = 'tx_calendarize_domain_model_event';
    protected string $itemFieldName = 'categories';

    public function __invoke(GenericActionAssignmentEvent $event)
    {
        if (CalendarController::class !== $event->getClassName() || 'searchAction' !== $event->getEventName()) {
            return;
        }
        if (!$this->checkConfiguration($event->getVariables()['configurations'] ?? [], $this->itemTableName)) {
            return;
        }
        $variables = $event->getVariables();
        $variables['extended']['categories'] = array_merge(
            $variables['extended']['categories'] ?? [],
            $this->getCategories($this->itemTableName, $this->itemFieldName)
        );

        $event->setVariables($variables);
    }

    /**
     * Check if the event configuration is active.
     *
     * @param array  $configurations
     * @param string $tableName
     *
     * @return bool
     */
    protected function checkConfiguration(array $configurations, string $tableName): bool
    {
        foreach ($configurations as $config) {
            if (($config['tableName'] ?? '') === $tableName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets all used categories of the default Event (self::itemTableName).
     *
     * @param string $tableName
     * @param string $fieldName
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function getCategories(string $tableName, string $fieldName): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_category');

        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        $languageUid = GeneralUtility::makeInstance(Context::class)->getAspect('language')->getId();

        $queryBuilder->select('sys_category.*')
            ->groupBy('sys_category.uid')
            ->from('sys_category')
            ->join(
                'sys_category',
                'sys_category_record_mm',
                'sys_category_record_mm',
                $queryBuilder->expr()->eq(
                    'sys_category_record_mm.uid_local',
                    $queryBuilder->quoteIdentifier('sys_category.uid')
                )
            )
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        'sys_category_record_mm.tablenames',
                        $queryBuilder->createNamedParameter($tableName, \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'sys_category_record_mm.fieldname',
                        $queryBuilder->createNamedParameter($fieldName, \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->in(
                        'sys_category.sys_language_uid',
                        $queryBuilder->createNamedParameter([-1, $languageUid], Connection::PARAM_INT_ARRAY)
                    )
                )
            )
            ->orderBy('sys_category.title', 'ASC');

        return $queryBuilder->execute()->fetchAll();
    }
}
