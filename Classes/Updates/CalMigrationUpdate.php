<?php

/**
 * CalMigrationUpdate
 */

namespace HDNET\Calendarize\Updates;

use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\AbstractUpdate;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * CalMigrationUpdate
 *
 * If using the slots please use the m with func_get_args!
 * Example:
 * /**
 *  * @signalClass \HDNET\Calendarize\Updates\CalMigrationUpdate
 *  * @signalName getCalendarizeEventUid
 *  *
 *  *@return array
 *  *
 * public function getCalendarizeEventUid()
 * {
 *    $args = func_get_args();
 *    list($table, $dbQueries, $call) = $args;
 *
 *    $variables = [
 *        'table'     => self::EVENT_TABLE,
 *        'dbQueries' => $dbQueries
 *    ];
 *
 *    return $variables;
 * }
 *
 */
class CalMigrationUpdate extends AbstractUpdate
{

    /**
     * Import prefix
     */
    const IMPORT_PREFIX = 'calMigration:';

    /**
     * Event table
     */
    const EVENT_TABLE = 'tx_calendarize_domain_model_event';

    /**
     * Configuration table
     */
    const CONFIGURATION_TABLE = 'tx_calendarize_domain_model_configuration';

    /**
     * The human-readable title of the upgrade wizard
     *
     * @var string
     */
    protected $title = 'Migrate cal event structures to the new calendarize event structures. 
    Try to migrate all cal information and place the new calendarize event models in the same folder 
    as the cal-records. Please note: the migration will be create calendarize default models.';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     *
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        $nonMigratedCalIds = $this->getNonMigratedCalIds();
        $count = count($nonMigratedCalIds);
        if ($count === 0) {
            return false;
        }
        $description = "There " . ($count > 1 ? 'are ' . $count : 'is ' . $count) . " non migrated EXT:cal event
        " . ($count > 1 ? 's' : '') . ". Run the update process to migrate the events to EXT:calendarize events.";
        return true;
    }

    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries      Queries done in this update
     * @param mixed &$customMessages Custom messages
     *
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        $calIds = $this->getNonMigratedCalIds();
        $this->performSysCategoryUpdate($calIds, $dbQueries, $customMessages);
        $this->performSysFileReferenceUpdate($calIds, $dbQueries, $customMessages);
        $this->performCalEventUpdate($calIds, $dbQueries, $customMessages);
        $this->performLinkEventToCategory($calIds, $dbQueries, $customMessages);

        return true;
    }

    /**
     * @param       $calIds
     * @param array $dbQueries
     * @param       $customMessages
     *
     * @return bool
     */
    public function performCalEventUpdate($calIds, array &$dbQueries, &$customMessages)
    {
        $db = HelperUtility::getDatabaseConnection();

        $events = $db->exec_SELECTgetRows('*', 'tx_cal_event', 'uid IN (' . implode(',', $calIds) . ')');
        foreach ($events as $event) {
            $calendarizeEventRecord = [
                'pid'         => $event['pid'],
                'import_id'   => self::IMPORT_PREFIX . (int)$event['uid'],
                'tstamp'      => $event['tstamp'],
                'crdate'      => $event['crdate'],
                'hidden'      => $event['hidden'],
                'starttime'   => $event['starttime'],
                'endtime'     => $event['endtime'],
                'title'       => $event['title'],
                'organizer'   => $event['organizer'],
                'location'    => $event['location'],
                'abstract'    => $event['teaser'],
                'description' => $event['description'],
                'images'      => $event['image'],
                'downloads'   => $event['attachment'],
                'calendarize' => $this->buildConfigurations($event, $dbQueries)
            ];

            $variables = [
                'calendarizeEventRecord' => $calendarizeEventRecord,
                'event'                  => $event,
                'table'                  => self::EVENT_TABLE,
                'dbQueries'              => $dbQueries
            ];

            /** @var Dispatcher $dispatcher */
            $dispatcher = HelperUtility::getSignalSlotDispatcher();
            $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PreInsert', $variables);

            $query = $db->INSERTquery($variables['table'], $variables['calendarizeEventRecord']);
            $db->admin_query($query);
            $dbQueries[] = $query;

            $variablesPostInsert = [
                'calendarizeEventRecord' => $calendarizeEventRecord,
                'event'                  => $event,
                'table'                  => $variables['table'],
                'recordId'               => $db->sql_insert_id(),
                'dbQueries'              => $dbQueries
            ];


            /** @var Dispatcher $dispatcher */
            $dispatcher = HelperUtility::getSignalSlotDispatcher();
            $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PostInsert', $variablesPostInsert);

        }


        /** @var IndexerService $indexer */
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();

        return true;
    }

    /**
     * Migrate the 'sys_file_reference' entries from 'tx_cal_event' to 'tx_calendarize_domain_model_event'.
     * Mark the imported entries with the import-id.
     *
     * @param       $calIds
     * @param array $dbQueries
     * @param       $customMessages
     */
    public function performSysFileReferenceUpdate($calIds, array &$dbQueries, &$customMessages)
    {
        $db = HelperUtility::getDatabaseConnection();

        $variables = [
            'table'      => 'tx_cal_event',
            'fieldnames' => ['image', 'attachment'],
            'dbQueries'  => $dbQueries,
            'calIds'     => $calIds
        ];

        // select all not migrated entries
        $fieldnames = 'fieldname = \'' . implode('\' OR fieldname = \'', $variables['fieldnames']) . '\'';
        $selectWhere = 'tablenames = \'' . $variables['table'] . '\' AND (' . $fieldnames . ')';
        $selectWhere .= ' AND NOT EXISTS (SELECT NULL FROM sys_file_reference sfr2 WHERE sfr2.import_id = CONCAT(\'' . self::IMPORT_PREFIX . '\', sfr1.uid))';

        $selectQuery = $db->SELECTquery('*', 'sys_file_reference sfr1', $selectWhere);
        $selectResults = $db->admin_query($selectQuery);
        $dbQueries[] = $selectQuery;

        $variables = [
            'table'         => self::EVENT_TABLE,
            'fieldnames'    => $variables['fieldnames'],
            'dbQueries'     => $dbQueries,
            'calIds'        => $calIds,
            'selectResults' => $selectResults
        ];

        // create new entry with import_id
        foreach ($variables['selectResults'] as $selectResult) {
            $selectResult['tablenames'] = $variables['table'];
            $selectResult['import_id'] = self::IMPORT_PREFIX . $selectResult['uid'];
            $selectResult['fieldname'] = ($selectResult['fieldname'] == 'image') ? 'images' : 'downloads';
            unset($selectResult['uid_foreign']);
            unset($selectResult['uid']);

            $insertQuery = $db->INSERTquery('sys_file_reference', $selectResult);
            $db->admin_query($insertQuery);
            $dbQueries[] = $insertQuery;
        }
    }

    /**
     * Migrate the 'tx_cal_category' table to the 'sys_category' table.
     *
     * @param       $calIds
     * @param array $dbQueries
     * @param       $customMessages
     */
    protected function performSysCategoryUpdate($calIds, array &$dbQueries, &$customMessages)
    {
        $db = HelperUtility::getDatabaseConnection();

        // first migrate from tx_cal_category to sys_category
        $variables = [
            'table'     => 'tx_cal_category',
            'dbQueries' => $dbQueries,
            'calIds'    => $calIds
        ];

        $selectWhere = '1 = 1 ' . BackendUtility::deleteClause($variables['table']);
        $selectQuery = $db->SELECTquery('*', $variables['table'], $selectWhere);
        $selectResults = $db->admin_query($selectQuery);
        $dbQueries[] = $selectQuery;

        foreach ($selectResults as $category) {
            $sysCategoryRecord = [
                'pid'              => $category['pid'],
                'tstamp'           => $category['tstamp'],
                'crdate'           => $category['crdate'],
                'cruser_id'        => $category['cruser_id'],
                'deleted'          => $category['deleted'],
                'hidden'           => $category['hidden'],
                'starttime'        => $category['starttime'],
                'endtime'          => $category['endtime'],
                'sys_language_uid' => $category['sys_language_uid'],
                'l10n_parent'      => $category['l18n_parent'],
                'l10n_diffsource'  => $category['l18n_diffsource'],
                'title'            => $category['title'],
                'parent'           => (int)$category['parent_category'],
                'import_id'        => self::IMPORT_PREFIX . (int)$category['uid'],
                'sorting'          => $category['sorting']
            ];

            $query = $db->INSERTquery('sys_category', $sysCategoryRecord);
            $db->admin_query($query);
            $dbQueries[] = $query;
        }

        // second rewrite the tree
        $variables = [
            'table'     => 'sys_category',
            'dbQueries' => $dbQueries,
            'calIds'    => $calIds
        ];

        $selectWhere = 'import_id != \'\'';
        $selectQuery = $db->SELECTquery('*', $variables['table'], $selectWhere);
        $selectResults = $db->admin_query($selectQuery);
        $dbQueries[] = $selectQuery;

        foreach ($selectResults as $sysCategory) {
            // update parent, because there are just the old uids
            $updateRecord = [
                'parent' => $this->getSysCategoryParentUid(self::IMPORT_PREFIX . (int) $sysCategory['parent'])
            ];
            $query = $db->UPDATEquery('sys_category', 'uid = ' . $sysCategory['uid'], $updateRecord);
            $db->admin_query($query);
            $dbQueries[] = $query;
        }
    }

    /**
     * Return the parentUid for the 'sys_category' entry on base of the import_id.
     *
     * @param string $importId
     *
     * @return int
     */
    protected function getSysCategoryParentUid($importId)
    {
        $db = HelperUtility::getDatabaseConnection();

        $selectWhere = 'import_id = \'' . $importId . '\'';
        $selectQuery = $db->SELECTquery('uid', 'sys_category', $selectWhere);
        $selectResults = $db->admin_query($selectQuery);
        $dbQueries[] = $selectQuery;

        $result = $db->sql_fetch_assoc($selectResults);
        $uid = (int) $result['uid'];

        return $uid;
    }

    /**
     * Link the Events to the migrated Categories.
     * This build up the 'sys_category_record_mm' table on base of the 'tx_cal_event_category_mm' table.
     *
     * @param $calIds
     * @param array $dbQueries
     * @param array $customMessages
     */
    public function performLinkEventToCategory($calIds, &$dbQueries, &$customMessages)
    {
        $db = HelperUtility::getDatabaseConnection();

        $selectQuery = $db->SELECTquery('*', 'tx_cal_event_category_mm', '1 = 1');
        $selectResults = $db->admin_query($selectQuery);
        $dbQueries[] = $selectQuery;

        $variables = [
            'tablenames'     => self::EVENT_TABLE,
            'fieldname' => 'categories',
            'dbQueries' => $dbQueries
        ];

        /** @var Dispatcher $dispatcher */
        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ , $variables);

        foreach ($selectResults as $mm) {
            $eventUid = $this->getCalendarizeEventUid(self::IMPORT_PREFIX . $mm['uid_local']);
            $categoryUid = $this->getCalendarizeCategoryUid(self::IMPORT_PREFIX . $mm['uid_foreign']);

            $insertValues = [
                'uid_local' => $categoryUid,
                'uid_foreign' => $eventUid,
                'tablenames' => $variables['tablenames'],
                'fieldname' => $variables['fieldname']
            ];

            $insertQuery = $db->INSERTquery('sys_category_record_mm ', $insertValues);
            $db->admin_query($insertQuery);
            $dbQueries[] = $insertQuery;
        }
    }

    /**
     * Get the event uid on base of the given import_id.
     * The import_id is the original tx_cal_event id prefixed with the IMPORT_PREFIX.
     *
     * @param string $importId
     * @param array $dbQueries
     * @param array $customMessages
     *
     * @return int
     */
    protected function getCalendarizeEventUid($importId, &$dbQueries, &$customMessages)
    {
        $db = HelperUtility::getDatabaseConnection();

        $variables = [
            'table'     => self::EVENT_TABLE,
            'dbQueries' => $dbQueries
        ];

        /** @var Dispatcher $dispatcher */
        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ , $variables);

        $selectWhere = 'import_id = \'' . $importId . '\'';
        $selectQuery = $db->SELECTquery('uid', $variables['table'], $selectWhere);
        $selectResults = $db->admin_query($selectQuery);
        $dbQueries[] = $selectQuery;

        $result = $db->sql_fetch_assoc($selectResults);
        $uid = (int) $result['uid'];
        return $uid;
    }

    /**
     * Get the sys_category uid on base of the given import_id.
     * The import_id is the original tx_cal_category id prefixed with the IMPORT_PREFIX.
     *
     * @see CalMigrationUpdate::IMPORT_PREFIX
     *
     * @param string $importId
     * @param array $dbQueries
     * @param array $customMessages
     *
     * @return int
     */
    protected function getCalendarizeCategoryUid($importId, &$dbQueries, &$customMessages)
    {
        $db = HelperUtility::getDatabaseConnection();

        $variables = [
            'table'     => 'sys_category',
            'dbQueries' => $dbQueries
        ];

        /** @var Dispatcher $dispatcher */
        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ , $variables);

        $selectWhere = 'import_id = \'' . $importId . '\'';
        $selectQuery = $db->SELECTquery('uid', $variables['table'], $selectWhere);
        $selectResults = $db->admin_query($selectQuery);
        $dbQueries[] = $selectQuery;

        $result = $db->sql_fetch_assoc($selectResults);
        $uid = (int) $result['uid'];
        return $uid;
    }

    /**
     * @param $calEventRow
     * @param $dbQueries
     *
     * @return int
     */
    protected function buildConfigurations($calEventRow, &$dbQueries)
    {
        $db = HelperUtility::getDatabaseConnection();
        $configurationRow = [
            'pid'        => $calEventRow['pid'],
            'tstamp'     => $calEventRow['tstamp'],
            'crdate'     => $calEventRow['crdate'],
            'type'       => 'time',
            'handling'   => 'include',
            'start_date' => $this->migrateDate($calEventRow['start_date']),
            'end_date'   => $this->migrateDate($calEventRow['end_date']),
            'start_time' => $calEventRow['start_time'],
            'end_time'   => $calEventRow['end_time'],
            'all_day'    => $calEventRow['allday'],

        ];

        $variables = [
            'table'            => self::CONFIGURATION_TABLE,
            'configurationRow' => $configurationRow,
            'calEventRow'      => $calEventRow,
            'dbQueries'        => $dbQueries
        ];

        /** @var Dispatcher $dispatcher */
        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PreInsert', $variables);

        $query = $db->INSERTquery($variables['table'], $variables['configurationRow']);
        $db->admin_query($query);
        $dbQueries[] = $query;
        $recordId = $db->sql_insert_id();

        $variables = [
            'table'            => $variables['table'],
            'configurationRow' => $configurationRow,
            'calEventRow'      => $calEventRow,
            'recordId'         => $recordId,
            'dbQueries'        => $dbQueries
        ];

        /** @var Dispatcher $dispatcher */
        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PostInsert', $variables);

        return $variables['recordId'];

        /*
         *
         * @todo
        frequency	text NULL
        till_date	int(11) [0]
        counter_amount	int(11) [0]
        counter_interval	int(11) [0]
        recurrence	text NULL
        day	text NULL


                 * ["freq"]=>
                 * string(4) "none"
                 * ["until"]=>
                 * string(1) "0"
                 * ["cnt"]=>
                 * string(1) "0"
                 * ["byday"]=>
                 * string(0) ""
                 * ["bymonthday"]=>
                 * string(0) ""
                 * ["bymonth"]=>
                 * string(0) ""
                 * ["intrval"]=>
                 * string(1) "1"
                 * ["rdate"]=>
                 * NULL
                 * ["rdate_type"]=>
                 * string(1) "0"
                 * ["deviation"]=>
                 * string(1) "0"
                 * ["monitor_cnt"]=>
                 * string(1) "0"
                 * ["exception_cnt"]=>
                 */
    }

    /**
     * @param $oldFormat
     *
     * @return int|string
     */
    protected function migrateDate($oldFormat)
    {
        try {
            $date = new \DateTime($oldFormat);
            return $date->getTimestamp();
        } catch (\Exception $e) {
        }
        return '';
    }

    /**
     * Get the non migrated cal IDs
     *
     * @return array
     */
    protected function getNonMigratedCalIds()
    {
        if (!ExtensionManagementUtility::isLoaded('cal')) {
            return [];
        }

        $db = HelperUtility::getDatabaseConnection();
        $checkImportIds = [];
        $nonMigrated = [];
        $events = $db->exec_SELECTgetRows('uid', 'tx_cal_event', '1=1' . BackendUtility::deleteClause('tx_cal_event'));

        foreach ($events as $event) {
            $checkImportIds[] = '"' . self::IMPORT_PREFIX . $event['uid'] . '"';
            $nonMigrated[(int)$event['uid']] = (int)$event['uid'];
        }

        $countOriginal = count($checkImportIds);
        if ($countOriginal === 0) {
            return [];
        }

        $variables = [
            'table' => self::EVENT_TABLE
        ];

        /** @var Dispatcher $dispatcher */
        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PreSelect', $variables);

        $migratedRows = $db->exec_SELECTgetRows(
            'uid,import_id',
            $variables['table'],
            'import_id IN (' . implode(',', $checkImportIds) . ')' . BackendUtility::deleteClause($variables['table'])
        );

        foreach ($migratedRows as $migratedRow) {
            $importId = (int)str_replace(self::IMPORT_PREFIX, '', $migratedRow['import_id']);
            if (isset($nonMigrated[$importId])) {
                unset($nonMigrated[$importId]);
            }
        }

        $variables = [
            'table'        => $variables['table'],
            'migratedRows' => $migratedRows,
            'nonMigrated'  => $nonMigrated
        ];

        /** @var Dispatcher $dispatcher */
        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'ReadyParsed', $variables);


        return $variables['nonMigrated'];
    }
}
