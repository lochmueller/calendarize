<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use HDNET\Calendarize\Service\IndexerService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;

#[UpgradeWizard('calendarize_populateEventSlugs')]
class PopulateEventSlugs extends AbstractUpdate
{
    protected string $title = 'Introduce URL parts ("slugs") to calendarize event model';

    protected string $description = 'Updates slug field of EXT:calendarize event records and runs a reindex';

    protected string $table = 'tx_calendarize_domain_model_event';

    protected string $fieldName = 'slug';

    /**
     * PopulateEventSlugs constructor.
     */
    public function __construct(
        protected IndexerService $indexerService,
    ) {}

    public function getIdentifier(): string
    {
        return 'calendarize_populateEventSlugs';
    }

    public function executeUpdate(): bool
    {
        $this->populateSlugs($this->table, $this->fieldName);
        $this->indexerService->reindexAll();

        return true;
    }

    /**
     * Populate the slug fields in the table using SlugHelper.
     */
    public function populateSlugs(string $table, string $field): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $statement = $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq($field, $queryBuilder->createNamedParameter('')),
                    $queryBuilder->expr()->isNull($field),
                ),
            )
            ->executeQuery();

        $fieldConfig = $GLOBALS['TCA'][$table]['columns'][$field]['config'];
        $evalInfo = !empty($fieldConfig['eval']) ? GeneralUtility::trimExplode(',', $fieldConfig['eval'], true) : [];
        $hasToBeUnique = \in_array('unique', $evalInfo, true);
        $hasToBeUniqueInSite = \in_array('uniqueInSite', $evalInfo, true);
        $hasToBeUniqueInPid = \in_array('uniqueInPid', $evalInfo, true);
        /** @var SlugHelper $slugHelper */
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, $table, $field, $fieldConfig);
        while ($record = $statement->fetchAssociative()) {
            $recordId = (int)$record['uid'];
            $pid = (int)$record['pid'];
            $slug = $slugHelper->generate($record, $pid);

            $state = RecordStateFactory::forName($table)
                ->fromArray($record, $pid, $recordId);
            if ($hasToBeUnique && !$slugHelper->isUniqueInTable($slug, $state)) {
                $slug = $slugHelper->buildSlugForUniqueInTable($slug, $state);
            }
            if ($hasToBeUniqueInSite && !$slugHelper->isUniqueInSite($slug, $state)) {
                $slug = $slugHelper->buildSlugForUniqueInSite($slug, $state);
            }
            if ($hasToBeUniqueInPid && !$slugHelper->isUniqueInPid($slug, $state)) {
                $slug = $slugHelper->buildSlugForUniqueInPid($slug, $state);
            }

            $connection->update(
                $table,
                [$field => $slug],
                ['uid' => $recordId],
            );
        }
    }

    /**
     * Check if any slug field in the table has an empty value.
     */
    protected function checkEmptySlug(string $table, string $field): bool
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $numberOfEntries = $queryBuilder
            ->count('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq($field, $queryBuilder->createNamedParameter('')),
                    $queryBuilder->expr()->isNull($field),
                ),
            )
            ->executeQuery()
            ->fetchOne();

        return $numberOfEntries > 0;
    }

    public function updateNecessary(): bool
    {
        return $this->checkEmptySlug($this->table, $this->fieldName);
    }

    /**
     * @return string[] All new fields and tables must exist
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}
