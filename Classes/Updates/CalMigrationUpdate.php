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

/**
 * CalMigrationUpdate
 */
class CalMigrationUpdate extends AbstractUpdate
{

    /**
     * Import prefix
     */
    const IMPORT_PREFIX = 'calMigration:';

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
        $db = HelperUtility::getDatabaseConnection();

        $events = $db->exec_SELECTgetRows('*', 'tx_cal_event', 'uid IN (' . implode(',', $calIds) . ')');
        foreach ($events as $event) {
            $calendarizeEventRecord = [
                'pid'         => $event['pid'],
                'import_id'   => self::IMPORT_PREFIX . $event['uid'],
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
                'calendarize' => $this->buildConfigurations($event, $dbQueries)
            ];

            /**
             * @todo
             * ["image"]=>
             * string(1) "0"
             * ["attachment"]=>
             * string(1) "0"
             */

            $query = $db->INSERTquery('tx_calendarize_domain_model_event', $calendarizeEventRecord);
            $db->admin_query($query);
            $dbQueries[] = $query;
        }


        /** @var IndexerService $indexer */
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();

        return true;
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
        $table = 'tx_calendarize_domain_model_configuration';
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

        $query = $db->INSERTquery($table, $configurationRow);
        $db->admin_query($query);
        $dbQueries[] = $query;

        return $db->sql_insert_id();

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

        $migratedRows = $db->exec_SELECTgetRows('uid,import_id', 'tx_calendarize_domain_model_event',
            'import_id IN (' . implode(',',
                $checkImportIds) . ')' . BackendUtility::deleteClause('tx_calendarize_domain_model_event'));

        foreach ($migratedRows as $migratedRow) {
            $importId = (int)str_replace(self::IMPORT_PREFIX, '', $migratedRow['import_id']);
            if (isset($nonMigrated[$importId])) {
                unset($nonMigrated[$importId]);
            }
        }
        return $nonMigrated;
    }
}
