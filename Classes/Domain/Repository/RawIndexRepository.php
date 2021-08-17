<?php

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use HDNET\Calendarize\Utility\WorkspaceUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RawIndexRepository
{
    protected $tableName = 'tx_calendarize_domain_model_index';

    /**
     * Find outdated events.
     */
    public function findOutdatedEvents(string $tableName, int $waitingPeriod): array
    {
        // calculate the waiting time
        $interval = 'P' . $waitingPeriod . 'D';
        $now = DateTimeUtility::getNow();
        $now->sub(new \DateInterval($interval));

        $q = HelperUtility::getDatabaseConnection($this->tableName)->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(HiddenRestriction::class));

        $q->select('foreign_uid')
            ->addSelectLiteral(
                $q->expr()->max('end_date', 'max_end_date')
            )
            ->from($this->tableName)
            ->where($q->expr()->eq('foreign_table', $q->createNamedParameter($tableName)))
            ->groupBy('foreign_uid')
            ->having(
                $q->expr()->lt('max_end_date', $q->createNamedParameter($now->format('Y-m-d')))
            );

        return $q->execute()->fetchAll();
    }

    /**
     * Find the next events (ignore enable fields).
     */
    public function findNextEvents(string $foreignTable, int $uid, int $limit = 5): array
    {
        $q = HelperUtility::getDatabaseConnection($this->tableName)->createQueryBuilder();

        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, WorkspaceUtility::getCurrentWorkspaceId()));

        $liveId = BackendUtility::getLiveVersionIdOfRecord($foreignTable, $uid);
        if (null !== $liveId) {
            $uid = $liveId;
        }

        $q->select('*')
            ->from($this->tableName)
            ->where(
                $q->expr()->andX(
                    $q->expr()->gte('start_date', $q->createNamedParameter(DateTimeUtility::getNow()->format('Y-m-d'))),
                    $q->expr()->eq('foreign_table', $q->createNamedParameter($foreignTable)),
                    $q->expr()->eq('foreign_uid', $q->createNamedParameter($uid, \PDO::PARAM_INT))
                )
            )
            ->addOrderBy('start_date', 'ASC')
            ->addOrderBy('start_time', 'ASC')
            ->setMaxResults($limit);

        $result = (array)$q->execute()->fetchAll();

        foreach ($result as $key => $row) {
            BackendUtility::workspaceOL($this->tableName, $row, WorkspaceUtility::getCurrentWorkspaceId());
            $result[$key] = $row;
        }

        return $result;
    }

    /**
     * Get the current items (ignore enable fields).
     */
    public function findAllEvents(string $tableName, int $uid, int $workspace = 0): array
    {
        $q = HelperUtility::getDatabaseConnection($this->tableName)->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, $workspace));

        $liveId = BackendUtility::getLiveVersionIdOfRecord($tableName, $uid);
        if (null !== $liveId) {
            $uid = $liveId;
        }

        $q->select('*')
            ->from($this->tableName)
            ->where(
                $q->expr()->eq('foreign_table', $q->createNamedParameter($tableName)),
                $q->expr()->eq('foreign_uid', $q->createNamedParameter($uid, \PDO::PARAM_INT))
            );

        return (array)$q->execute()->fetchAll();
    }

    /**
     * Get the current items (ignore enable fields).
     *
     * @see findAllEvents
     */
    public function countAllEvents(string $tableName, int $uid, int $workspace = 0): int
    {
        $q = HelperUtility::getDatabaseConnection($this->tableName)->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, WorkspaceUtility::getCurrentWorkspaceId()));

        $liveId = BackendUtility::getLiveVersionIdOfRecord($tableName, $uid);
        if (null !== $liveId) {
            $uid = $liveId;
        }

        $q->select('*')
            ->from($this->tableName)
            ->where(
                $q->expr()->eq('foreign_table', $q->createNamedParameter($tableName)),
                $q->expr()->eq('foreign_uid', $q->createNamedParameter($uid, \PDO::PARAM_INT))
            );

        return (int)$q->execute()->rowCount();
    }
}
