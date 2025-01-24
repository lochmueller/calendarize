<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use HDNET\Calendarize\Domain\Model\PluginConfiguration;
use HDNET\Calendarize\Event\IndexRepositoryDefaultConstraintEvent;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\MathUtility;

class CategoryConstraintEventListener
{
    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function __invoke(IndexRepositoryDefaultConstraintEvent $event): void
    {
        $indexTypes = $event->getIndexTypes();
        $foreignIds = $event->getForeignIds();

        if (empty($indexTypes)) {
            return;
        }

        $conjunction = strtolower($event->getAdditionalSlotArguments()['settings']['categoryConjunction'] ?? '');

        // If "ignore category selection" is used, nothing needs to be done
        // An empty value is assumed to be OR for backwards compatibility.
        if ('all' === $conjunction || !\in_array($conjunction, ['and', 'or', ''])) {
            return;
        }

        $categoryIds = $this->getCategoryIds($event->getAdditionalSlotArguments());
        if (empty($categoryIds)) {
            return;
        }

        $tables = $this->getTableNames($indexTypes, $foreignIds);

        $newIndexIds = $this->getIndexIds($categoryIds, $conjunction, $tables);

        $event->setForeignIds($foreignIds + $newIndexIds);
    }

    /**
     * Get the selected categories of the content configuration and plugin configuration model.
     *
     * @param array $additionalSlotArguments
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    protected function getCategoryIds(array $additionalSlotArguments): array
    {
        $table = 'sys_category_record_mm';
        $db = HelperUtility::getDatabaseConnection($table);
        $queryBuilder = $db->createQueryBuilder();

        $categoryIds = [];
        if (
            isset($additionalSlotArguments['contentRecord']['uid'])
            && MathUtility::canBeInterpretedAsInteger($additionalSlotArguments['contentRecord']['uid'])
        ) {
            $categoryIds = $queryBuilder->select('uid_local')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->and(
                        $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter('tt_content')),
                        $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('categories')),
                        $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($additionalSlotArguments['contentRecord']['uid'])),
                    ),
                )
                ->executeQuery()
                ->fetchFirstColumn();
        }

        if (
            isset($additionalSlotArguments['settings']['pluginConfiguration'])
            && $additionalSlotArguments['settings']['pluginConfiguration'] instanceof PluginConfiguration
        ) {
            /** @var PluginConfiguration $pluginConfiguration */
            $pluginConfiguration = $additionalSlotArguments['settings']['pluginConfiguration'];
            $categories = $pluginConfiguration->getCategories();
            foreach ($categories as $category) {
                $categoryIds[] = $category->getUid();
            }
        }

        // Remove duplicate IDs
        return array_unique($categoryIds);
    }

    /**
     * Get all table names for filtering.
     * Models with ids already set or without category field are ignored.
     *
     * @param array $indexTypes
     * @param array $foreignIds
     *
     * @return array
     */
    protected function getTableNames(array $indexTypes, array $foreignIds): array
    {
        $tables = [];

        foreach ($indexTypes as $type) {
            $tableName = Register::getRegister()[$type]['tableName'] ?? null;
            if (null === $tableName) {
                continue;
            }
            // Skip if there are already ids (e.g. by other extensions)
            // We don't want to overwrite such values
            if (isset($foreignIds[$tableName])) {
                continue;
            }

            // Check if the table has categories
            if (!isset($GLOBALS['TCA'][$tableName]['columns']['categories'])) {
                continue;
            }
            $tables[] = $tableName;
        }

        return $tables;
    }

    /**
     * Gets the index IDs (foreign tables and foreign UIDs) that have the categories set.
     *
     * @param array  $categories
     * @param string $conjunction
     * @param array  $tables
     *
     * @return array
     *
     * @throws Exception
     */
    protected function getIndexIds(array $categories, string $conjunction, array $tables): array
    {
        $uidLocal_field = 'uid_local'; // Category uid
        $uidForeign_field = 'uid_foreign'; // Event uid
        $tableNames = 'tablenames';
        $MM_table = 'sys_category_record_mm';

        $queryBuilder = HelperUtility::getDatabaseConnection($MM_table)
            ->createQueryBuilder();
        $queryBuilder->select($tableNames, $uidForeign_field)
            ->from($MM_table)
            ->groupBy($tableNames, $uidForeign_field)
            ->where(
                $queryBuilder->expr()->eq(
                    'fieldname',
                    $queryBuilder->createNamedParameter('categories'),
                ),
                $queryBuilder->expr()->in(
                    $tableNames,
                    $queryBuilder->createNamedParameter($tables, ArrayParameterType::STRING),
                ),
            );

        switch ($conjunction) {
            case 'and':
                // Relational Algebra
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->in(
                        $uidLocal_field,
                        $queryBuilder->createNamedParameter($categories, ArrayParameterType::INTEGER),
                    ),
                )->having(
                    'COUNT(DISTINCT ' . $queryBuilder->quoteIdentifier($uidLocal_field) . ') = '
                    . $queryBuilder->createNamedParameter(\count($categories), Connection::PARAM_INT),
                );
                break;

            case 'or':
            default:
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->in(
                        $uidLocal_field,
                        $queryBuilder->createNamedParameter($categories, ArrayParameterType::INTEGER),
                    ),
                );
        }

        $result = $queryBuilder->executeQuery();

        $indexIds = [];
        while ($row = $result->fetchAssociative()) {
            $indexIds[$row[$tableNames]][] = $row[$uidForeign_field];
        }

        // Enforce no result for all tables without a match
        foreach ($tables as $table) {
            if (empty($indexIds[$table])) {
                $indexIds[$table] = [-1];
            }
        }

        return $indexIds;
    }
}
