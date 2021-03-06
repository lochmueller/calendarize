<?php

/**
 * Index the given events.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Event\IndexAllEvent;
use HDNET\Calendarize\Event\IndexPreUpdateEvent;
use HDNET\Calendarize\Event\IndexSingleEvent;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Service\Url\SlugService;
use HDNET\Calendarize\Utility\ArrayUtility;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var IndexPreparationService
     */
    protected $preparationService;

    /**
     * @var SlugService
     */
    protected $slugService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        IndexPreparationService $preparationService,
        SlugService $slugService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->preparationService = $preparationService;
        $this->slugService = $slugService;
    }

    /**
     * Reindex all elements.
     */
    public function reindexAll()
    {
        $this->eventDispatcher->dispatch(new IndexAllEvent($this, IndexAllEvent::POSITION_PRE));

        $this->removeInvalidConfigurationIndex();

        foreach (Register::getRegister() as $key => $configuration) {
            $tableName = $configuration['tableName'];
            $this->removeInvalidRecordIndex($tableName);

            $q = HelperUtility::getDatabaseConnection($tableName)->createQueryBuilder();
            $q->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

            $q->select('uid')
                ->from($tableName);

            $transPointer = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? false; // e.g. l10n_parent
            if ($transPointer) {
                // Note: In localized tables, it is important, that the "default language records" are indexed first, so the
                // overlays can connect with l10n_parent to the right default record.
                $q->orderBy((string)$transPointer);
            }
            $rows = $q->execute()->fetchAll();
            foreach ($rows as $row) {
                $this->updateIndex($key, $tableName, (int)$row['uid']);
            }
        }
        $this->eventDispatcher->dispatch(new IndexAllEvent($this, IndexAllEvent::POSITION_POST));
    }

    /**
     * Reindex the given element.
     *
     * @param string $configurationKey
     * @param string $tableName
     * @param int    $uid
     */
    public function reindex(string $configurationKey, string $tableName, int $uid)
    {
        $this->eventDispatcher->dispatch(new IndexSingleEvent($configurationKey, $tableName, $uid, $this, IndexSingleEvent::POSITION_PRE));

        $this->removeInvalidConfigurationIndex();
        $this->removeInvalidRecordIndex($tableName);
        $this->updateIndex($configurationKey, $tableName, $uid);

        $this->eventDispatcher->dispatch(new IndexSingleEvent($configurationKey, $tableName, $uid, $this, IndexSingleEvent::POSITION_POST));
    }

    /**
     * Get index count.
     *
     * @param string $tableName
     * @param int    $uid
     *
     * @return int
     */
    public function getIndexCount(string $tableName, $uid): int
    {
        // Note: "uid" could be e.g. NEW6273482 in DataHandler process
        if (MathUtility::canBeInterpretedAsInteger($uid)) {
            return (int)$this->getCurrentItems($tableName, (int)$uid)->rowCount();
        }

        return 0;
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

        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $q->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $q->expr()->andX(
                    $q->expr()->gte('start_date', $q->createNamedParameter(DateTimeUtility::getNow()->format('Y-m-d'))),
                    $q->expr()->eq('foreign_table', $q->createNamedParameter($table)),
                    $q->expr()->eq('foreign_uid', $q->createNamedParameter((int)$uid, \PDO::PARAM_INT))
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
    protected function updateIndex(string $configurationKey, string $tableName, int $uid)
    {
        $neededItems = $this->preparationService->prepareIndex($configurationKey, $tableName, $uid);
        $this->insertAndUpdateNeededItems($neededItems, $tableName, $uid);
    }

    /**
     * Get the current items (ignore enable fields).
     *
     * @param string $tableName
     * @param int    $uid
     *
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    protected function getCurrentItems(string $tableName, int $uid)
    {
        $q = HelperUtility::getDatabaseConnection(self::TABLE_NAME)->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $q->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $q->expr()->eq('foreign_table', $q->createNamedParameter($tableName)),
                $q->expr()->eq('foreign_uid', $q->createNamedParameter($uid, \PDO::PARAM_INT))
            );

        return $q->execute();
    }

    /**
     * Insert and/or update the needed index records.
     *
     * @param array  $neededItems
     * @param string $tableName
     * @param int    $uid
     */
    protected function insertAndUpdateNeededItems(array $neededItems, string $tableName, int $uid)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection(self::TABLE_NAME);
        $currentItems = $this->getCurrentItems($tableName, $uid)->fetchAll();

        $event = new IndexPreUpdateEvent($neededItems, $tableName, $uid);
        $this->eventDispatcher->dispatch($event);

        foreach ($event->getNeededItems() as $neededKey => $neededItem) {
            foreach ($currentItems as $currentKey => $currentItem) {
                if (ArrayUtility::isEqualArray($neededItem, $currentItem, ['tstamp', 'crdate', 'slug'])) {
                    // Check if the current slug starts with the new slug
                    // Prevents regeneration for slugs with counting suffixes (added before insertion)
                    // False positives are possible (e.g. single event where a part gets removed)
                    if (0 !== mb_stripos($currentItem['slug'] ?? '', $neededItem['slug'], 0, 'utf-8')) {
                        // Slug changed
                        continue;
                    }

                    unset($neededItems[$neededKey], $currentItems[$currentKey]);

                    break;
                }
            }
        }
        foreach ($currentItems as $item) {
            $databaseConnection->delete(self::TABLE_NAME, ['uid' => $item['uid']]);
        }

        $this->generateSlugAndInsert($neededItems);
    }

    /**
     * Generates a slug and inserts the records in the db.
     *
     * @param array $neededItems
     */
    protected function generateSlugAndInsert(array $neededItems): void
    {
        $db = HelperUtility::getDatabaseConnection(self::TABLE_NAME);
        foreach ($neededItems as $key => $item) {
            $item['slug'] = $this->slugService->makeSlugUnique($item);
            // We need to insert after each index, so subsequent indices do not get the same slug
            $db->insert(self::TABLE_NAME, $item);
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

        $q = HelperUtility::getDatabaseConnection(self::TABLE_NAME)->createQueryBuilder();
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

        $validKeys = array_keys(Register::getRegister());
        if ($validKeys) {
            foreach ($validKeys as $key => $value) {
                $validKeys[$key] = $q->createNamedParameter($value);
            }

            $q->delete(self::TABLE_NAME)
                ->where(
                    $q->expr()->notIn('unique_register_key', $validKeys)
                )->execute();

            return (bool)$q->execute();
        }

        return (bool)$db->truncate(self::TABLE_NAME);
    }
}
