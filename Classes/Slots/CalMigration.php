<?php
/**
 * CalMigration.
 */
namespace HDNET\Calendarize\Slots;

use HDNET\Calendarize\Updates\CalMigrationUpdate;
use HDNET\Calendarize\Utility\HelperUtility;

/**
 * CalMigration.
 */
class CalMigration
{
    /**
     * Update the sys_file_reference table for tx_cal_event files like images.
     *
     * @see \HDNET\Calendarize\Updates\CalMigrationUpdate::performCalEventUpdate()
     *
     * @signalClass \HDNET\Calendarize\Updates\CalMigrationUpdate
     * @signalName performCalEventUpdatePostInsert
     *
     * @return array
     */
    public function updateSysFileReference()
    {
        $args = func_get_args();
        list($calendarizeEventRecord, $event, $table, $recordId, $dbQueries) = $args;

        $db = HelperUtility::getDatabaseConnection();

        $selectWhere = 'tablenames = \'tx_cal_event\' AND uid_foreign = ' . (int) $event['uid'];
        $query = $db->SELECTquery('*', 'sys_file_reference', $selectWhere);
        $selectResults = $db->admin_query($query);
        $dbQueries[] = $query;

        foreach ($selectResults as $selectResult) {
            $updateWhere = ' import_id = \'' . CalMigrationUpdate::IMPORT_PREFIX . $selectResult['uid'] . '\'';
            $fieldValues = [
                'uid_foreign' => (int) $recordId,
                'tablenames' => $table,
            ];

            $query = $db->UPDATEquery('sys_file_reference', $updateWhere, $fieldValues);
            $db->admin_query($query);
            $dbQueries[] = $query;
        }

        $variables = [
            'calendarizeEventRecord' => $calendarizeEventRecord,
            'event' => $event,
            'table' => $table,
            'recordId' => $recordId,
            'dbQueries' => $dbQueries,
        ];

        return $variables;
    }
}
