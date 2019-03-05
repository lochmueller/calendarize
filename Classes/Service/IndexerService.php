<?php

/**
 * Index the given events.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\ArrayUtility;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Index the given events.
 */
class IndexerService extends AbstractService
{
    /**
     * Index table name.
     */
    const TABLE_NAME = 'tx_calendarize_domain_model_index';

    /**
     * Reindex all elements.
     */
    public function reindexAll()
    {
        $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $dispatcher->dispatch(__CLASS__, __FUNCTION__, [$this]);

        $this->removeInvalidConfigurationIndex();
        $q = HelperUtility::getDatabaseConnection(self::TABLE_NAME)->createQueryBuilder();

        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        foreach (Register::getRegister() as $key => $configuration) {
            $tableName = $configuration['tableName'];
            $this->removeInvalidRecordIndex($tableName);

            $q->resetQueryParts();

            $transPointer = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? false; // e.g. l10n_parent

            if ($transPointer) {
                // Note: In loclized tables, it is important, that the "default language records" are indexed first, so the
                // overlays can connect with l10n_paretn to the right default record.
                $q->select('uid')
                    ->from($tableName)
                    ->orderBy((string) $transPointer);
            } else {
                $q->select('uid')
                    ->from($tableName);
            }

            $rows = $q->execute()->fetchAll();
            foreach ($rows as $row) {
                $this->updateIndex($key, $configuration['tableName'], $row['uid']);
            }
        }
    }

    /**
     * Reindex the given element.
     *
     * @param string $configurationKey
     * @param string $tableName
     * @param int    $uid
     */
    public function reindex($configurationKey, $tableName, $uid)
    {
        $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $dispatcher->dispatch(__CLASS__, __FUNCTION__, [$configurationKey, $tableName, $uid, $this]);

        $this->removeInvalidConfigurationIndex();
        $this->removeInvalidRecordIndex($tableName);
        $this->updateIndex($configurationKey, $tableName, $uid);
    }

    /**
     * Get index count.
     *
     * @param $table
     * @param $uid
     *
     * @return mixed
     */
    public function getIndexCount($table, $uid)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection($table);

        return $databaseConnection->count('*', self::TABLE_NAME, [
            'foreign_table' => $table,
            'foreign_uid' => (int) $uid,
        ]);
    }

    /**
     * Get the next events.
     *
     * @param string $table
     * @param int    $uid
     * @param int    $limit
     *
     * @return array|null
     */
    public function getNextEvents($table, $uid, $limit = 5)
    {
        $q = HelperUtility::getDatabaseConnection($table)->createQueryBuilder();
        $now = DateTimeUtility::getNow();
        $now->setTime(0, 0, 0);

        $q->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $q->expr()->andX(
                    $q->expr()->gte('start_date', $now->getTimestamp()),
                    $q->expr()->eq('foreign_table', $q->createNamedParameter($table)),
                    $q->expr()->eq('foreign_uid', $q->createNamedParameter((int) $uid, \PDO::PARAM_INT))
                )
            )
            ->addOrderBy('start_date', 'ASC')
            ->addOrderBy('start_time', 'ASC')
            ->setMaxResults($limit);

        return $q->execute()->fetchAll();
    }

    /**
     * Build the index for one element.
     *
     * @param string $configurationKey
     * @param string $tableName
     * @param int    $uid
     */
    protected function updateIndex($configurationKey, $tableName, $uid)
    {
        /** @var $preparationService IndexPreparationService */
        static $preparationService = null;
        if (null === $preparationService) {
            $preparationService = GeneralUtility::makeInstance(IndexPreparationService::class);
        }
        $neededItems = $preparationService->prepareIndex($configurationKey, $tableName, $uid);
        $this->insertAndUpdateNeededItems($neededItems, $tableName, $uid);
    }

    /**
     * Insert and/or update the needed index records.
     *
     * @param array  $neededItems
     * @param string $tableName
     * @param int    $uid
     */
    protected function insertAndUpdateNeededItems(array $neededItems, $tableName, $uid)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection($tableName);

        $currentItems = $databaseConnection->select(['*'], self::TABLE_NAME, [
            'foreign_table' => $tableName,
            'foreign_uid' => $uid,
        ])->fetchAll();

        foreach ($neededItems as $neededKey => $neededItem) {
            $remove = false;
            foreach ($currentItems as $currentKey => $currentItem) {
                if (ArrayUtility::isEqualArray($neededItem, $currentItem)) {
                    $remove = true;
                    unset($neededItems[$neededKey], $currentItems[$currentKey]);

                    break;
                }
            }
            if ($remove) {
                continue;
            }
        }
        foreach ($currentItems as $item) {
            $databaseConnection->delete(self::TABLE_NAME, ['uid' => $item['uid']]);
        }

        $neededItems = \array_values($neededItems);
        if ($neededItems) {
            $databaseConnection->bulkInsert(self::TABLE_NAME, $neededItems, \array_keys($neededItems[0]));
        }
    }

    /**
     * Remove Index items of the given table of records
     * that are deleted or do not exists anymore.
     *
     * @param string $tableName
     */
    protected function removeInvalidRecordIndex($tableName)
    {
        $q = HelperUtility::getDatabaseConnection($tableName)->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $q->select('uid')
            ->from($tableName);

        $rows = $q->execute()->fetchAll();

        $q->resetQueryParts()->resetRestrictions();
        $q->delete(self::TABLE_NAME)
            ->where(
                $q->expr()->eq('foreign_table', $q->createNamedParameter($tableName))
            );

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = $row['uid'];
        }
        if ($ids) {
            $q->andWhere(
                $q->expr()->notIn('foreign_uid', $ids)
            );
        }

        $q->execute();
    }

    /**
     * Remove index Items of configurations that are not valid anymore.
     *
     * @return bool
     */
    protected function removeInvalidConfigurationIndex()
    {
        $db = HelperUtility::getDatabaseConnection(self::TABLE_NAME);
        $q = $db->createQueryBuilder();

        $validKeys = \array_keys(Register::getRegister());
        if ($validKeys) {
            foreach ($validKeys as $key => $value) {
                $validKeys[$key] = $q->createNamedParameter($value);
            }

            $q->delete(self::TABLE_NAME)
                ->where(
                    $q->expr()->notIn('unique_register_key', $validKeys)
                )->execute();

            return (bool) $q->execute();
        }

        return (bool) $db->truncate(self::TABLE_NAME);
    }
}
