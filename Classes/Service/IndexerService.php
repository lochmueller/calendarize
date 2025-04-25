<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Repository\RawIndexRepository;
use HDNET\Calendarize\Event\IndexAllEvent;
use HDNET\Calendarize\Event\IndexPreUpdateEvent;
use HDNET\Calendarize\Event\IndexSingleEvent;
use HDNET\Calendarize\Event\ModifyIndexingQueryBuilderEvent;
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
use TYPO3\CMS\Core\Versioning\VersionState;

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

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected IndexPreparationService $preparationService,
        protected SlugService $slugService,
        protected RawIndexRepository $rawIndexRepository,
    ) {}

    /**
     * Reindex all elements.
     */
    public function reindexAll(): void
    {
        $this->logger->debug('Start reindex ALL process');

        $this->eventDispatcher->dispatch(new IndexAllEvent($this, IndexAllEvent::POSITION_PRE));

        $this->removeInvalidConfigurationIndex();

        foreach (Register::getRegister() as $key => $configuration) {
            $tableName = $configuration['tableName'];
            $this->removeInvalidRecordIndex($tableName);

            $queryBuilder = HelperUtility::getQueryBuilder($tableName);
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

            $queryBuilder
                ->select('uid')
                ->from($tableName);

            $worksSpaceSupport = $GLOBALS['TCA'][$tableName]['ctrl']['versioningWS']
                && (bool)$GLOBALS['TCA'][$tableName]['ctrl']['versioningWS'];
            if ($worksSpaceSupport) {
                $queryBuilder->addOrderBy('t3ver_wsid', 'ASC');
            }

            $transPointer = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? false; // e.g. l10n_parent
            if ($transPointer) {
                // Note: In localized tables, it is important, that the "default language records" are indexed first,
                // so the overlays can connect with l10n_parent to the right default record.
                $queryBuilder->addOrderBy((string)$transPointer, 'ASC');
            }

            $event = new ModifyIndexingQueryBuilderEvent($queryBuilder, $configuration);
            $this->eventDispatcher->dispatch($event);
            $queryBuilder = $event->getQueryBuilder();

            $rows = $queryBuilder
                ->executeQuery()
                ->fetchAllAssociative();
            foreach ($rows as $row) {
                $this->updateIndex($key, $tableName, (int)$row['uid']);
            }
        }

        $this->eventDispatcher->dispatch(new IndexAllEvent($this, IndexAllEvent::POSITION_POST));
    }

    /**
     * Reindex the given element.
     */
    public function reindex(string $configurationKey, string $tableName, int $uid): void
    {
        $this->logger->debug('Start reindex SINGLE ' . $tableName . ':' . $uid);

        $this->eventDispatcher->dispatch(
            new IndexSingleEvent($configurationKey, $tableName, $uid, $this, IndexSingleEvent::POSITION_PRE),
        );

        $this->removeInvalidConfigurationIndex();
        $this->removeInvalidRecordIndex($tableName);
        $this->updateIndex($configurationKey, $tableName, $uid);

        $this->eventDispatcher->dispatch(
            new IndexSingleEvent($configurationKey, $tableName, $uid, $this, IndexSingleEvent::POSITION_POST),
        );
    }

    /**
     * Build the index for one element.
     */
    protected function updateIndex(string $configurationKey, string $tableName, int $uid): void
    {
        $rawRecord = BackendUtility::getRecord($tableName, $uid);

        $checkUid = $uid;
        $liveId = BackendUtility::getLiveVersionIdOfRecord($tableName, $uid);
        if (null !== $liveId) {
            $checkUid = $liveId;
        }

        $workspace = isset($rawRecord['t3ver_wsid']) ? (int)$rawRecord['t3ver_wsid'] : 0;

        if (VersionState::DELETE_PLACEHOLDER === ($rawRecord['t3ver_state'] ?? false)) {
            // Remove all entries in current workspace that are related to the current item
            $this->rawIndexRepository->deleteByIdentifier([
                't3ver_wsid' => $workspace,
                'foreign_table' => $tableName,
                'foreign_uid' => $checkUid,
            ]);

            return;
        }

        if ($workspace && null !== $liveId && $liveId !== $uid) {
            // Update live in front of versions
            $this->reindex($configurationKey, $tableName, $liveId);
        }

        $neededItems = $this->preparationService->prepareIndex($configurationKey, $tableName, $uid);

        $this->logger->debug('Update index of ' . $tableName . ':' . $uid . ' in  workspace ' . $workspace);

        if ($workspace) {
            // Remove all entries in current workspace that are related to the current item
            $this->rawIndexRepository->deleteByIdentifier([
                't3ver_wsid' => $workspace,
                'foreign_table' => $tableName,
                'foreign_uid' => $checkUid,
            ]);

            // Create deleted items for every entry in the live workspace
            $liveItems = $this->rawIndexRepository->findAllEvents($tableName, $checkUid);

            foreach ($liveItems as $liveItem) {
                $liveItem['t3ver_state'] = VersionState::DELETE_PLACEHOLDER;
                $liveItem['t3ver_oid'] = $liveItem['uid'];
                $liveItem['t3ver_wsid'] = $workspace;
                unset($liveItem['uid']);
                $this->rawIndexRepository->insert($liveItem);
            }
        }

        $this->insertAndUpdateNeededItems($neededItems, $tableName, $uid, $workspace);
    }

    /**
     * Insert and/or update the needed index records.
     */
    protected function insertAndUpdateNeededItems(
        array $neededItems,
        string $tableName,
        int $uid,
        int $workspace = 0,
    ): void {
        $currentItems = $this->rawIndexRepository->findAllEvents($tableName, $uid, $workspace);

        if ($workspace) {
            // Placeholder are respect in function updateIndex
            $currentItems = array_filter($currentItems, static function ($item) {
                return VersionState::DELETE_PLACEHOLDER !== ($item['t3ver_state'] ?? false);
            });
        }

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
                    if (!empty($neededItem['slug']) && 0 !== mb_stripos($currentItem['slug'] ?? '', $neededItem['slug'], 0, 'utf-8')) {
                        // Slug changed
                        continue;
                    }

                    unset($neededItems[$neededKey], $currentItems[$currentKey]);

                    break;
                }
            }
        }

        foreach ($currentItems as $item) {
            // If select workspace, drop the workspace records
            $this->rawIndexRepository->deleteByIdentifier(['uid' => $item['_ORIG_uid'] ?? $item['uid']]);
        }

        $this->generateSlugAndInsert($neededItems, $workspace);
    }

    /**
     * Generates a slug and inserts the records in the db.
     */
    protected function generateSlugAndInsert(array $neededItems, int $workspace = 0): void
    {
        $i = 0;
        foreach ($neededItems as $item) {
            if ($workspace) {
                $item['t3ver_oid'] = 0;
                $item['t3ver_state'] = VersionState::NEW_PLACEHOLDER;
            }

            $item['slug'] = $this->slugService->makeSlugUnique($item, $i);
            // We need to insert after each index, so subsequent indices do not get the same slug
            $this->rawIndexRepository->insert($item);
            ++$i;
        }
    }

    /**
     * Remove Index items of the given table of records
     * that are deleted or do not exist anymore.
     */
    protected function removeInvalidRecordIndex(string $tableName): void
    {
        $queryBuilder = HelperUtility::getDatabaseConnection($tableName)->createQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $queryBuilder->select('uid')
            ->from($tableName);

        $rows = $queryBuilder
            ->executeQuery()
            ->fetchAllAssociative();

        $queryBuilder = HelperUtility::getDatabaseConnection(self::TABLE_NAME)->createQueryBuilder();
        $queryBuilder->delete(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('foreign_table', $queryBuilder->createNamedParameter($tableName)),
            );

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = $row['uid'];
        }
        if ($ids) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->notIn('foreign_uid', $ids),
            );
        }

        $queryBuilder->executeStatement();
    }

    /**
     * Remove index Items of configurations that are not valid anymore.
     */
    protected function removeInvalidConfigurationIndex(): bool
    {
        $this->logger->debug('Log invalid index items of old configurations');

        $validKeys = array_keys(Register::getRegister());
        if ($validKeys) {
            return $this->rawIndexRepository->deleteNotInUniqueRegisterKey($validKeys);
        }

        return $this->rawIndexRepository->truncate();
    }
}
