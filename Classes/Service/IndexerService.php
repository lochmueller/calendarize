<?php

/**
 * Index the given events.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Repository\RawIndexRepository;
use HDNET\Calendarize\Event\IndexAllEvent;
use HDNET\Calendarize\Event\IndexPreUpdateEvent;
use HDNET\Calendarize\Event\IndexSingleEvent;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Service\Url\SlugService;
use HDNET\Calendarize\Utility\ArrayUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Index the given events.
 */
class IndexerService extends AbstractService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Index table name.
     */
    public const TABLE_NAME = 'tx_calendarize_domain_model_index';

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var IndexPreparationService
     */
    protected $preparationService;

    /**
     * @var RawIndexRepository
     */
    protected $rawIndexRepository;

    /**
     * @var SlugService
     */
    protected $slugService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        IndexPreparationService $preparationService,
        SlugService $slugService,
        RawIndexRepository $rawIndexRepository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->preparationService = $preparationService;
        $this->slugService = $slugService;
        $this->rawIndexRepository = $rawIndexRepository;
    }

    /**
     * Reindex all elements.
     */
    public function reindexAll()
    {
        $this->logger->debug('Start reindex ALL process');

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

            $worksSpaceSupport = $GLOBALS['TCA'][$tableName]['ctrl']['versioningWS'] ? (bool)$GLOBALS['TCA'][$tableName]['ctrl']['versioningWS'] : false;
            if ($worksSpaceSupport) {
                $q->addOrderBy('t3ver_wsid', 'ASC');
            }

            $transPointer = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? false; // e.g. l10n_parent
            if ($transPointer) {
                // Note: In localized tables, it is important, that the "default language records" are indexed first, so the
                // overlays can connect with l10n_parent to the right default record.
                $q->addOrderBy((string)$transPointer, 'ASC');
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
        $this->logger->debug('Start reindex SINGLE ' . $tableName . ':' . $uid);

        $this->eventDispatcher->dispatch(new IndexSingleEvent($configurationKey, $tableName, $uid, $this, IndexSingleEvent::POSITION_PRE));

        $this->removeInvalidConfigurationIndex();
        $this->removeInvalidRecordIndex($tableName);
        $this->updateIndex($configurationKey, $tableName, $uid);
        $this->reindexVersions($configurationKey, $tableName, $uid);

        $this->eventDispatcher->dispatch(new IndexSingleEvent($configurationKey, $tableName, $uid, $this, IndexSingleEvent::POSITION_POST));
    }

    protected function reindexVersions(string $configurationKey, string $tableName, int $uid)
    {
        $record = BackendUtility::getRecord($tableName, $uid);
        if (0 === (int)($record['t3ver_oid'] ?? 0)) {
            $versions = (array)BackendUtility::selectVersionsOfRecord($tableName, $uid, 'uid', null);
            $ids = array_map(function ($row) {
                return (int)$row['uid'];
            }, $versions);

            $ids = array_filter($ids, function ($id) use ($uid) {
                return $id !== $uid;
            });

            foreach ($ids as $id) {
                $this->reindex($configurationKey, $tableName, $id);
            }
        }
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

        $rawRecord = BackendUtility::getRecord($tableName, $uid);
        $workspace = isset($rawRecord['t3ver_wsid']) ? (int)$rawRecord['t3ver_wsid'] : 0;
        $origId = isset($rawRecord['t3ver_oid']) ? (int)$rawRecord['t3ver_oid'] : 0;

        $this->logger->debug('Update index of ' . $tableName . ':' . $uid . ' in  workspace ' . $workspace);

        if ($workspace && $origId) {
            // Remove all entries in current workspace that are related to the current item
            HelperUtility::getDatabaseConnection(self::TABLE_NAME)->delete(self::TABLE_NAME, [
                't3ver_wsid' => $workspace,
                'foreign_table' => $tableName,
                'foreign_uid' => $origId,
            ]);

            // Create deleted items for very entry in the live workspace
            $liveItems = $this->rawIndexRepository->findAllEvents($tableName, $origId, 0);
            foreach ($liveItems as $liveItem) {
                $liveItem['t3ver_state'] = '2';
                $liveItem['t3ver_oid'] = $liveItem['uid'];
                $liveItem['t3ver_wsid'] = $workspace;
                $liveItem['foreign_uid'] = $origId;
                unset($liveItem['uid']);
                HelperUtility::getDatabaseConnection(self::TABLE_NAME)->insert(self::TABLE_NAME, $liveItem);
            }
        }

        $this->insertAndUpdateNeededItems($neededItems, $tableName, $uid, $workspace);
    }

    /**
     * Insert and/or update the needed index records.
     *
     * @param array  $neededItems
     * @param string $tableName
     * @param int    $uid
     */
    protected function insertAndUpdateNeededItems(array $neededItems, string $tableName, int $uid, int $workspace = 0)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection(self::TABLE_NAME);
        $currentItems = $this->rawIndexRepository->findAllEvents($tableName, $uid, $workspace);

        $event = new IndexPreUpdateEvent($neededItems, $tableName, $uid);
        $this->eventDispatcher->dispatch($event);

        // use return of event for further processing
        $neededItems = $event->getNeededItems();

        foreach ($neededItems as $neededKey => $neededItem) {
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
            // @todo check "_ORIG_uid"
            $databaseConnection->delete(self::TABLE_NAME, ['uid' => $item['uid']]);
            // Delete workspace versions
            $databaseConnection->delete(self::TABLE_NAME, ['t3ver_oid' => $item['uid']]);
        }

        $this->generateSlugAndInsert($neededItems, $workspace);
    }

    /**
     * Generates a slug and inserts the records in the db.
     *
     * @param array $neededItems
     */
    protected function generateSlugAndInsert(array $neededItems, int $workspace = 0): void
    {
        $db = HelperUtility::getDatabaseConnection(self::TABLE_NAME);
        foreach ($neededItems as $key => $item) {
            if ($workspace) {
                $item['t3ver_oid'] = 0;
            }

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
        $this->logger->debug('Log invalid index items of old configurations');

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
