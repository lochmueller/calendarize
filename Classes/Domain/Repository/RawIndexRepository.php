<?php

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;

class RawIndexRepository extends AbstractRawRepository
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

        $q = $this->getQueryBuilder();
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
    public function findEventsAfterStartDate(string $foreignTable, int $uid, \DateTime $dateTime, int $limit = 5, int $workspace = 0): array
    {
        $q = $this->getQueryBuilder();

        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, $workspace));

        $liveId = BackendUtility::getLiveVersionIdOfRecord($foreignTable, $uid);
        if (null !== $liveId) {
            $uid = $liveId;
        }

        $q->select('*')
            ->from($this->tableName)
            ->where(
                $q->expr()->andX(
                    $q->expr()->gte('start_date', $q->createNamedParameter($dateTime->format('Y-m-d'))),
                    $q->expr()->eq('foreign_table', $q->createNamedParameter($foreignTable)),
                    $q->expr()->eq('foreign_uid', $q->createNamedParameter($uid, \PDO::PARAM_INT))
                )
            )
            ->addOrderBy('start_date', 'ASC')
            ->addOrderBy('start_time', 'ASC')
            ->setMaxResults($limit);

        $result = (array)$q->execute()->fetchAll();

        foreach ($result as $key => $row) {
            BackendUtility::workspaceOL($this->tableName, $row, $workspace);
            $result[$key] = $row;
        }

        // @todo check
        $result = array_values(array_filter($result, static function ($item) {
            return \is_array($item) && VersionState::DELETE_PLACEHOLDER !== ($item['t3ver_state'] ?? false);
        }));

        return $result;
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

    public function deleteNotInUniqueRegisterKey(array $validKeys)
    {
        $q = $this->getQueryBuilder();

        foreach ($validKeys as $key => $value) {
            $validKeys[$key] = $q->createNamedParameter($value);
        }

        $q->delete($this->tableName)
            ->where(
                $q->expr()->notIn('unique_register_key', $validKeys)
            )->execute();

        return (bool)$q->execute();
    }
}
