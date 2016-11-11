<?php
/**
 * CalMigration
 *
 * @author  Carsten Biebricher <cabi@lernziel.de>
 */

namespace HDNET\Calendarize\Slots;


/**
 * CalMigration
 *
 * @author Carsten Biebricher <cabi@lernziel.de>
 */
class CalMigration
{
    /**
     * Update the sys_file_reference table for tx_cal_event images.
     *
     * @signalClass \HDNET\Calendarize\Updates\CalMigrationUpdate
     * @signalName performUpdatePostInsert
     *
     * @see \HDNET\Calendarize\Updates\CalMigrationUpdate::performUpdate()
     *
     * @param array  $calendarizeEventRecord
     * @param array  $event
     * @param string $table
     * @param int    $recordId
     * @param array &$dbQueries      Queries done in this update
     *
     * @return array
     */
    public function updateSysFileReferenceForImages($calendarizeEventRecord, $event, $table, $recordId, &$dbQueries)
    {
        $db = HelperUtility::getDatabaseConnection();

        $where = 'tablenames = \'tx_cal_event\' AND uid_foreign = ' . (int) $event['uid'] . ' AND fieldname = \'image\'';
        $fieldValues = [
            'uid_foreign' => (int) $recordId,
            'tablenames' => $table,
            'fieldname' => 'images'
        ];
        $query = $db->UPDATEquery('sys_file_reference', $where, $fieldValues);
        $db->admin_query($query);

        $dbQueries[] = $query;

        $variables = [
            'calendarizeEventRecord' => $calendarizeEventRecord,
            'event' => $event,
            'table' => $table,
            'recordId' => $recordId,
            'dbQueries' => $dbQueries
        ];

        return $variables;
    }

    /**
     * Update the sys_file_reference table for tx_cal_event attachments.
     *
     * @signalClass \HDNET\Calendarize\Updates\CalMigrationUpdate
     * @signalName performUpdatePostInsert
     *
     * @see \HDNET\Calendarize\Updates\CalMigrationUpdate::performUpdate()
     *
     * @param array  $calendarizeEventRecord
     * @param array  $event
     * @param string $table
     * @param int    $recordId
     * @param array &$dbQueries      Queries done in this update
     *
     * @return array
     */
    public function updateSysFileReferenceForAttachments($calendarizeEventRecord, $event, $table, $recordId, &$dbQueries)
    {
        $db = HelperUtility::getDatabaseConnection();

        $where = 'tablenames = \'tx_cal_event\' AND uid_foreign = ' . (int) $event['uid'] . ' AND fieldname = \'attachment\'';
        $fieldValues = [
            'uid_foreign' => (int) $recordId,
            'tablenames' => $table,
            'fieldname' => 'downloads'
        ];
        $query = $db->UPDATEquery('sys_file_reference', $where, $fieldValues);
        $db->admin_query($query);

        $dbQueries[] = $query;

        $variables = [
            'calendarizeEventRecord' => $calendarizeEventRecord,
            'event' => $event,
            'table' => $table,
            'recordId' => $recordId,
            'dbQueries' => $dbQueries
        ];

        return $variables;
    }
}