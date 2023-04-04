<?php

/**
 * CalMigration.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Slots;

use HDNET\Calendarize\Updates\CalMigrationUpdate;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CalMigration.
 */
class CalMigration
{
    /**
     * Update the sys_file_reference table for tx_cal_event files like images.
     *
     * @see CalMigrationUpdate::performCalEventUpdate()
     */
    public function updateSysFileReference(): array
    {
        $args = func_get_args();
        list($calendarizeEventRecord, $event, $table, $recordId, $dbQueries) = $args;

        $q = HelperUtility::getQueryBuilder('sys_file_reference');
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $q->select('*')
            ->from('sys_file_reference')
            ->where(
                $q->expr()->and(
                    $q->expr()->eq('tablenames', $q->quote('tx_cal_event')),
                    $q->expr()->eq('uid_foreign', $q->createNamedParameter((int)$event['uid'], \PDO::PARAM_INT))
                )
            );

        $dbQueries[] = HelperUtility::queryWithParams($q);
        $selectResults = $q
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($selectResults as $selectResult) {
            $q->resetQueryParts();

            $importId = CalMigrationUpdate::IMPORT_PREFIX . $selectResult['uid'];

            $q->update('sys_file_reference')
                ->where(
                    $q->expr()->eq('import_id', $q->createNamedParameter($importId))
                )
                ->set('uid_foreign', (int)$recordId)
                ->set('tablenames', $table);

            $dbQueries[] = HelperUtility::queryWithParams($q);

            $q->executeStatement();
        }

        return [
            'calendarizeEventRecord' => $calendarizeEventRecord,
            'event' => $event,
            'table' => $table,
            'recordId' => $recordId,
            'dbQueries' => $dbQueries,
        ];
    }
}
