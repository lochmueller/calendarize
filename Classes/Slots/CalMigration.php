<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Slots;

use HDNET\Calendarize\Updates\CalMigrationUpdate;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// @todo: still in use?
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

        $queryBuilder = HelperUtility::getQueryBuilder('sys_file_reference');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder->select('*')
            ->from('sys_file_reference')
            ->where(
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('tablenames', $queryBuilder->quote('tx_cal_event')),
                    $queryBuilder->expr()->eq(
                        'uid_foreign',
                        $queryBuilder->createNamedParameter((int)$event['uid'], \PDO::PARAM_INT)
                    )
                )
            );

        $dbQueries[] = HelperUtility::queryWithParams($queryBuilder);
        $selectResults = $queryBuilder
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($selectResults as $selectResult) {
            $queryBuilder->resetQueryParts();

            $importId = CalMigrationUpdate::IMPORT_PREFIX . $selectResult['uid'];

            $queryBuilder->update('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->eq('import_id', $queryBuilder->createNamedParameter($importId))
                )
                ->set('uid_foreign', (int)$recordId)
                ->set('tablenames', $table);

            $dbQueries[] = HelperUtility::queryWithParams($queryBuilder);

            $queryBuilder->executeStatement();
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
