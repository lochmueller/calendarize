<?php

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;

class RawIndexRepository extends AbstractRawRepository
{
    protected string $tableName = 'tx_calendarize_domain_model_index';

    /**
     * Find outdated events.
     */
    public function findOutdatedEvents(string $tableName, int $waitingPeriod): array
    {
        // calculate the waiting time
        $interval = 'P' . $waitingPeriod . 'D';
        $now = DateTimeUtility::getNow();
        $now->sub(new \DateInterval($interval));

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(HiddenRestriction::class));

        $queryBuilder->select('foreign_uid')
            ->addSelectLiteral(
                $queryBuilder->expr()->max('end_date', 'max_end_date'),
            )
            ->from($this->tableName)
            ->where($queryBuilder->expr()->eq('foreign_table', $queryBuilder->createNamedParameter($tableName)))
            ->groupBy('foreign_uid')
            ->having(
                $queryBuilder->expr()->lt('max_end_date', $queryBuilder->createNamedParameter($now->format('Y-m-d'))),
            );

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    /**
     * Find the next events (ignore enable fields).
     */
    public function findNextEvents(string $foreignTable, int $uid, int $limit = 5, int $workspace = 0): array
    {
        return $this->findEventsAfterStartDate($foreignTable, $uid, DateTimeUtility::getNow(), $limit, $workspace);
    }

    /**
     * Get the current items (ignore enable fields).
     */
    public function findAllEvents(string $tableName, int $uid, int $workspace = 0): array
    {
        return $this->findEventsAfterStartDate($tableName, $uid, new \DateTime('1970-01-01'), 999999, $workspace);
    }

    /**
     * Find the next events (ignore enable fields).
     */
    public function findEventsAfterStartDate(
        string $foreignTable,
        int $uid,
        \DateTime $dateTime,
        int $limit = 5,
        int $workspace = 0,
    ): array {
        $queryBuilder = $this->getQueryBuilder();

        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, $workspace));

        $liveId = BackendUtility::getLiveVersionIdOfRecord($foreignTable, $uid);
        if (null !== $liveId) {
            $uid = $liveId;
        }

        $queryBuilder->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->gte(
                        'start_date',
                        $queryBuilder->createNamedParameter($dateTime->format('Y-m-d')),
                    ),
                    $queryBuilder->expr()->eq(
                        'foreign_table',
                        $queryBuilder->createNamedParameter($foreignTable),
                    ),
                    $queryBuilder->expr()->eq(
                        'foreign_uid',
                        $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT),
                    ),
                ),
            )
            ->addOrderBy('start_date', 'ASC')
            ->addOrderBy('start_time', 'ASC')
            ->addOrderBy('uid', 'ASC')
            ->setMaxResults($limit);

        $result = $queryBuilder->executeQuery()->fetchAllAssociative();

        foreach ($result as $key => $row) {
            BackendUtility::workspaceOL($this->tableName, $row, $workspace);
            $result[$key] = $row;
        }

        // @todo check
        return array_values(
            array_filter(
                $result,
                static function ($item) {
                    return \is_array($item) && VersionState::DELETE_PLACEHOLDER !== ($item['t3ver_state'] ?? false);
                },
            ),
        );
    }

    /**
     * Get the current items (ignore enable fields).
     *
     * @see findAllEvents
     */
    public function countAllEvents(string $tableName, int $uid, int $workspace = 0): int
    {
        // Select all to check workspaces in the right way
        return \count($this->findAllEvents($tableName, $uid, $workspace));
    }

    public function deleteNotInUniqueRegisterKey(array $validKeys): bool
    {
        $queryBuilder = $this->getQueryBuilder();

        foreach ($validKeys as $key => $value) {
            $validKeys[$key] = $queryBuilder->createNamedParameter($value);
        }

        $queryBuilder->delete($this->tableName)
            ->where(
                $queryBuilder->expr()->notIn('unique_register_key', $validKeys),
            );

        return (bool)$queryBuilder->executeStatement();
    }
}
