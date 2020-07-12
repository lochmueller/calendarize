<?php

/**
 * CalMigrationUpdate.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use HDNET\Autoloader\Annotation\SignalClass;
use HDNET\Autoloader\Annotation\SignalName;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * CalMigrationUpdate.
 *
 * If using the slots please use the m with func_get_args!
 * Example:
 * /**
 *  * @SignalClass \HDNET\Calendarize\Updates\CalMigrationUpdate
 *  * @SignalName getCalendarizeEventUid
 *  *
 *
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
 */
class CalMigrationUpdate implements UpgradeWizardInterface
{
    /**
     * Import prefix.
     */
    const IMPORT_PREFIX = 'calMigration:';

    /**
     * Event table.
     */
    const EVENT_TABLE = 'tx_calendarize_domain_model_event';

    /**
     * Configuration table.
     */
    const CONFIGURATION_TABLE = 'tx_calendarize_domain_model_configuration';

    /**
     * ConfigurationGroup table.
     */
    const CONFIGURATION_GROUP_TABLE = 'tx_calendarize_domain_model_configurationgroup';

    /**
     * The human-readable title of the upgrade wizard.
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
        $count = \count($nonMigratedCalIds);
        if (0 === $count) {
            return false;
        }
        $description = 'There ' . ($count > 1 ? 'are ' . $count : 'is ' . $count) . ' non migrated EXT:cal event
        ' . ($count > 1 ? 's' : '') . '. Run the update process to migrate the events to EXT:calendarize events.';

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
    public function executeUpdate(): bool
    {
        $calIds = $this->getNonMigratedCalIds();
        $this->performSysCategoryUpdate($calIds, $dbQueries, $customMessages);
        $this->performSysFileReferenceUpdate($calIds, $dbQueries, $customMessages);
        $this->performExceptionEventUpdate($calIds, $dbQueries, $customMessages);
        $this->performCalEventUpdate($calIds, $dbQueries, $customMessages);
        $this->performLinkEventToCategory($calIds, $dbQueries, $customMessages);
        $this->performLinkEventToConfigurationGroup($calIds, $dbQueries, $customMessages);

        return true;
    }

    /**
     * Perform CAL event update.
     *
     * @param       $calIds
     * @param array $dbQueries
     * @param       $customMessages
     *
     * @return bool
     */
    public function performCalEventUpdate($calIds, array &$dbQueries, &$customMessages)
    {
        $table = 'tx_cal_event';
        $db = HelperUtility::getDatabaseConnection($table);
        $q = $db->createQueryBuilder();

        $events = $q->select('*')->from($table)->where(
            $q->expr()->in('uid', $calIds)
        )->execute()->fetchAll();

        foreach ($events as $event) {
            $calendarizeEventRecord = [
                'pid' => $event['pid'],
                'import_id' => self::IMPORT_PREFIX . (int)$event['uid'],
                'tstamp' => $event['tstamp'],
                'crdate' => $event['crdate'],
                'hidden' => $event['hidden'],
                'starttime' => $event['starttime'],
                'endtime' => $event['endtime'],
                'title' => $event['title'],
                'organizer' => $event['organizer'],
                'location' => $event['location'],
                'abstract' => $event['teaser'],
                'description' => $event['description'],
                'images' => $event['image'],
                'downloads' => $event['attachment'],
                'calendarize' => $this->buildConfigurations($event, $dbQueries),
            ];

            $variables = [
                'calendarizeEventRecord' => $calendarizeEventRecord,
                'event' => $event,
                'table' => self::EVENT_TABLE,
                'dbQueries' => $dbQueries,
            ];

            $dispatcher = HelperUtility::getSignalSlotDispatcher();
            $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PreInsert', $variables);

            $q->insert($variables['table'])->values($variables['calendarizeEventRecord']);
            $dbQueries[] = $q->getSQL();

            $q->execute();

            $variablesPostInsert = [
                'calendarizeEventRecord' => $calendarizeEventRecord,
                'event' => $event,
                'table' => $variables['table'],
                'recordId' => $db->lastInsertId($variables['table']),
                'dbQueries' => $dbQueries,
            ];

            $dispatcher = HelperUtility::getSignalSlotDispatcher();
            $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PostInsert', $variablesPostInsert);
        }

        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();

        return true;
    }

    /**
     * Perform exception event update.
     *
     * @param       $calIds
     * @param array $dbQueries
     * @param array $customMessages
     *
     * @return bool
     */
    public function performExceptionEventUpdate($calIds, &$dbQueries, &$customMessages)
    {
        $table = 'tx_cal_exception_event_group';
        // ConfigurationGroup für jede ExceptionGroup
        $db = HelperUtility::getDatabaseConnection($table);
        $q = $db->createQueryBuilder();
        $variables = [
            'table' => $table,
            'dbQueries' => $dbQueries,
            'calIds' => $calIds,
        ];

        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $q->select('*')->from($variables['table']);

        $selectResults = $q->execute()->fetchAll();
        $dbQueries[] = $q->getSQL();

        foreach ($selectResults as $selectResult) {
            $group = [
                'pid' => $selectResult['pid'],
                'tstamp' => $selectResult['tstamp'],
                'crdate' => $selectResult['crdate'],
                'cruser_id' => $selectResult['cruser_id'],
                'title' => $selectResult['title'],
                'configurations' => $this->getExceptionConfigurationForExceptionGroup($selectResult['uid'], $dbQueries),
                'hidden' => $selectResult['hidden'],
                'import_id' => self::IMPORT_PREFIX . $selectResult['uid'],
            ];

            $q->insert(self::CONFIGURATION_GROUP_TABLE)->values($group);
            $dbQueries[] = $q->getSQL();

            $q->execute();
        }

        return true;
    }

    /**
     * Perform link event to configuration group.
     *
     * @param $calIds
     * @param $dbQueries
     * @param $customMessages
     *
     * @return bool
     */
    public function performLinkEventToConfigurationGroup($calIds, &$dbQueries, &$customMessages)
    {
        $db = HelperUtility::getDatabaseConnection(self::CONFIGURATION_GROUP_TABLE);
        $q = $db->createQueryBuilder();
        $now = new \DateTime();

        $variables = [
            'table' => self::CONFIGURATION_GROUP_TABLE,
            'dbQueries' => $dbQueries,
            'calIds' => $calIds,
        ];

        $selectResults = $q->select('*')->from($variables['table'])->execute()->fetchAll();
        $dbQueries[] = $q->getSQL();

        foreach ($selectResults as $group) {
            $importId = \explode(':', $group['import_id']);
            $groupId = (int)$importId[1];

            $variables = [
                'table' => 'tx_cal_exception_event_mm',
                'dbQueries' => $dbQueries,
                'calIds' => $calIds,
            ];

            $q->resetQueryParts()->resetRestrictions();

            $q->select('uid_local')
                ->from($variables['table'])
                ->where(
                    $q->expr()->andX(
                        $q->expr()->eq('tablenames', 'tx_cal_exception_event_group'),
                        $q->expr()->eq('uid_foreign', $q->createNamedParameter((int)$groupId, \PDO::PARAM_INT))
                    )
                );

            $dbQueries[] = $q->getSQL();
            $selectResults = $q->execute()->fetchAll();

            foreach ($selectResults as $eventUid) {
                $eventImportId = self::IMPORT_PREFIX . (int)$eventUid['uid_local'];
                $configurationRow = [
                    'pid' => (int)$group['pid'],
                    'tstamp' => $now->getTimestamp(),
                    'crdate' => $now->getTimestamp(),
                    'type' => 'group',
                    'handling' => 'exclude',
                    'groups' => $group['uid'],
                ];

                $this->updateEventWithConfiguration($eventImportId, $configurationRow, $dbQueries, $customMessages);
            }
        }

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
        $db = HelperUtility::getDatabaseConnection('tx_cal_event');
        $q = $db->createQueryBuilder();

        $variables = [
            'table' => 'tx_cal_event',
            'fieldnames' => ['image', 'attachment'],
            'dbQueries' => $dbQueries,
            'calIds' => $calIds,
        ];

        // select all not migrated entries
        $fieldnames = 'fieldname = \'' . \implode('\' OR fieldname = \'', $variables['fieldnames']) . '\'';
        $selectWhere = 'tablenames = \'' . $variables['table'] . '\' AND (' . $fieldnames . ')';
        $selectWhere .= ' AND NOT EXISTS (SELECT NULL FROM sys_file_reference sfr2 WHERE sfr2.import_id = CONCAT(\'' . self::IMPORT_PREFIX . '\', sfr1.uid))';

        $q->select('*')
            ->from('sys_file_reference', 'sfr1')
            ->where($selectWhere);

        $dbQueries[] = $q->getSQL();
        $selectResults = $q->execute()->fetchAll();

        $variables = [
            'table' => self::EVENT_TABLE,
            'fieldnames' => $variables['fieldnames'],
            'dbQueries' => $dbQueries,
            'calIds' => $calIds,
            'selectResults' => $selectResults,
        ];

        // create new entry with import_id
        foreach ($variables['selectResults'] as $selectResult) {
            $selectResult['tablenames'] = $variables['table'];
            $selectResult['import_id'] = self::IMPORT_PREFIX . $selectResult['uid'];
            $selectResult['fieldname'] = ('image' === $selectResult['fieldname']) ? 'images' : 'downloads';
            unset($selectResult['uid_foreign'], $selectResult['uid']);

            $q->resetQueryParts()->resetRestrictions();
            $q->insert('sys_file_reference')->values($selectResult);

            $dbQueries[] = $q->getSQL();

            $q->execute();
        }
    }

    /**
     * Link the Events to the migrated Categories.
     * This build up the 'sys_category_record_mm' table on base of the 'tx_cal_event_category_mm' table.
     *
     * @param       $calIds
     * @param array $dbQueries
     * @param array $customMessages
     */
    public function performLinkEventToCategory($calIds, &$dbQueries, &$customMessages)
    {
        $table = 'tx_cal_event_category_mm';

        $db = HelperUtility::getDatabaseConnection($table);
        $q = $db->createQueryBuilder();

        $q->select('*')->from($table);
        $dbQueries[] = $q->getSQL();

        $selectResults = $q->execute()->fetchAll();

        $variables = [
            'tablenames' => self::EVENT_TABLE,
            'fieldname' => 'categories',
            'dbQueries' => $dbQueries,
        ];

        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        foreach ($selectResults as $mm) {
            $eventUid = $this->getCalendarizeEventUid(self::IMPORT_PREFIX . $mm['uid_local'], $dbQueries, $customMessages);
            $categoryUid = $this->getCalendarizeCategoryUid(
                self::IMPORT_PREFIX . $mm['uid_foreign'],
                $dbQueries,
                $customMessages
            );

            $insertValues = [
                'uid_local' => $categoryUid,
                'uid_foreign' => $eventUid,
                'tablenames' => $variables['tablenames'],
                'fieldname' => $variables['fieldname'],
            ];

            $q->insert('sys_category_record_mm')->values($insertValues);
            $dbQueries[] = $q->getSQL();

            $q->execute();
        }
    }

    /**
     * Update event with configuration.
     *
     * @param $eventImportId
     * @param $configuration
     * @param $dbQueries
     * @param $customMessages
     *
     * @return array
     */
    protected function updateEventWithConfiguration($eventImportId, $configuration, &$dbQueries, &$customMessages)
    {
        $db = HelperUtility::getDatabaseConnection(self::CONFIGURATION_TABLE);
        $q = $db->createQueryBuilder();

        $configurationRow = $this->findEventExcludeConfiguration($eventImportId, $dbQueries, $customMessages);
        if ($configurationRow) {
            $configurationRow['groups'] = $this->addValueToCsv($configurationRow['groups'], $configuration['groups']);

            unset($configurationRow['uid']);

            $q->update(self::CONFIGURATION_GROUP_TABLE)
                ->where('uid', $q->createNamedParameter((int)$configuration['uid'], \PDO::PARAM_INT))
                ->values($configurationRow);

            $dbQueries[] = $q->getSQL();
            $results = $q->execute();
        } else {
            $q->insert(self::CONFIGURATION_TABLE)->values($configuration);
            $dbQueries[] = $q->getSQL();

            $configurationId = $db->lastInsertId(self::CONFIGURATION_TABLE);

            $results = $this->addConfigurationIdToEvent($eventImportId, $configurationId, $dbQueries, $customMessages);
        }

        return $results;
    }

    /**
     * Add Value to CSV.
     *
     * @param string $csv
     * @param string $value
     *
     * @return string
     */
    protected function addValueToCsv($csv, $value)
    {
        $csvArray = GeneralUtility::trimExplode(',', $csv);

        // check for doubles
        $values = \array_flip($csvArray);
        if (isset($values[$value])) {
            return $csv;
        }
        $csvArray[] = $value;
        $csv = \implode(',', $csvArray);

        return $csv;
    }

    /**
     * Add configuration ID to event.
     *
     * @param string $eventImportId
     * @param int    $configurationId
     * @param array  $dbQueries
     * @param array  $customMessages
     *
     * @return array|bool
     */
    protected function addConfigurationIdToEvent($eventImportId, $configurationId, &$dbQueries, &$customMessages)
    {
        $event = $this->findEventByImportId($eventImportId, $dbQueries, $customMessages);
        if (!$event) {
            return false;
        }

        $event['calendarize'] = $this->addValueToCsv($event['calendarize'], $configurationId);

        return $this->updateEvent($event['uid'], $event, $dbQueries, $customMessages);
    }

    /**
     * Update event.
     *
     * @param int   $eventId
     * @param array $values
     * @param array $dbQueries
     * @param array $customMessages
     *
     * @return array
     */
    protected function updateEvent($eventId, $values, &$dbQueries, &$customMessages)
    {
        $db = HelperUtility::getDatabaseConnection(self::EVENT_TABLE);
        $q = $db->createQueryBuilder();

        $variables = [
            'table' => self::EVENT_TABLE,
            'eventId' => (int)$eventId,
            'values' => $values,
            'dbQueries' => $dbQueries,
        ];

        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        $q->update($variables['table'])
            ->where(
                $q->expr()->eq('uid', $q->createNamedParameter((int)$eventId, \PDO::PARAM_INT))
            )
            ->values($variables['values']);

        unset($values['uid']);

        $dbQueries[] = $q->getSQL();

        return $q->execute()->fetchAll();
    }

    /**
     * Find event by import ID.
     *
     * @param $eventImportId
     * @param $dbQueries
     * @param $customMessages
     *
     * @return array|bool
     */
    protected function findEventByImportId($eventImportId, &$dbQueries, &$customMessages)
    {
        $db = HelperUtility::getDatabaseConnection(self::EVENT_TABLE);
        $q = $db->createQueryBuilder();

        $variables = [
            'table' => self::EVENT_TABLE,
            'dbQueries' => $dbQueries,
            'eventImportId' => $eventImportId,
        ];

        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        $q->select('*')->from($variables['table'])
            ->where(
                $q->expr()->eq('import_id', $q->createNamedParameter($eventImportId))
            );

        $dbQueries[] = $q->getSQL();

        return $q->execute()->fetchAll();
    }

    /**
     * Find event exclude configuration.
     *
     * @param string $eventImportId
     * @param array  $dbQueries
     * @param array  $customMessages
     *
     * @return array|bool
     */
    protected function findEventExcludeConfiguration($eventImportId, &$dbQueries, &$customMessages)
    {
        $event = $this->findEventByImportId($eventImportId, $dbQueries, $customMessages);

        if (!$event) {
            return false;
        }

        $variables = [
            'table' => self::CONFIGURATION_TABLE,
            'dbQueries' => $dbQueries,
            'event' => $event,
        ];

        $db = HelperUtility::getDatabaseConnection($variables['table']);
        $q = $db->createQueryBuilder();

        $q->select('*')
            ->from($variables['table'])
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('type', 'group'),
                    $q->expr()->eq('handling', 'exclude'),
                    $q->expr()->in('uid', $variables['event']['calendarize'])
                )
            );

        $dbQueries[] = $q->getSQL();

        return $q->execute()->fetchAll();
    }

    /**
     * Get exception configuration for exception group.
     *
     * @param       $groupId
     * @param array $dbQueries
     *
     * @return string
     */
    protected function getExceptionConfigurationForExceptionGroup($groupId, &$dbQueries)
    {
        $recordIds = [];
        $variables = [
            'table' => 'tx_cal_exception_event_group_mm',
            'dbQueries' => $dbQueries,
        ];

        $db = HelperUtility::getDatabaseConnection($variables['table']);
        $q = $db->createQueryBuilder();

        $q->select('*')
            ->from($variables['table'])
            ->where('uid_local', $q->createNamedParameter((int)$groupId, \PDO::PARAM_INT));

        $dbQueries[] = $q->getSQL();

        $mmResults = $q->execute()->fetchAll();
        foreach ($mmResults as $mmResult) {
            $variables = [
                'table' => 'tx_cal_exception_event',
                'dbQueries' => $dbQueries,
            ];

            $q->resetQueryParts()->resetRestrictions();
            $q->select('*')
                ->from($variables['table'])
                ->where(
                    $q->expr()->eq('uid', $q->createNamedParameter((int)$mmResult['uid_foreign'], \PDO::PARAM_INT))
                );

            $dbQueries[] = $q->getSQL();

            $selectResults = $q->execute()->fetchAll();

            foreach ($selectResults as $selectResult) {
                $configurationRow = [
                    'pid' => $selectResult['pid'],
                    'tstamp' => $selectResult['tstamp'],
                    'crdate' => $selectResult['crdate'],
                    'type' => 'time',
                    'handling' => 'include',
                    'start_date' => $this->migrateDate($selectResult['start_date']),
                    'end_date' => $this->migrateDate($selectResult['end_date']),
                    'start_time' => (int)$selectResult['start_time'],
                    'end_time' => (int)$selectResult['end_time'],
                    'all_day' => (null === $selectResult['start_time'] && null === $selectResult['end_time']) ? 1 : 0,
                    'frequency' => $this->mapFrequency($selectResult['freq']),
                    'till_date' => $this->migrateDate($selectResult['until']),
                    'counter_amount' => (int)$selectResult['cnt'],
                    'counter_interval' => (int)$selectResult['interval'],
                    'import_id' => self::IMPORT_PREFIX . $selectResult['uid'],
                ];

                $variables = [
                    'table' => self::CONFIGURATION_TABLE,
                    'configurationRow' => $configurationRow,
                ];

                $dispatcher = HelperUtility::getSignalSlotDispatcher();
                $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PreInsert', $variables);

                $q->resetQueryParts()->resetRestrictions();
                $q->insert($variables['table'])->values($variables['configurationRow']);

                $dbQueries[] = $q->getSQL();

                $q->execute();

                $recordIds[] = $db->lastInsertId($variables['table']);
            }
        }

        return \implode(',', $recordIds);
    }

    /**
     * Map frequency.
     *
     * @param string $calFrequency
     *
     * @return string
     */
    protected function mapFrequency($calFrequency)
    {
        $freq = [
            'none' => null,
            'day' => 'daily',
            'week' => 'weekly',
            'month' => 'monthly',
            'year' => 'yearly',
        ];

        if (!isset($freq[$calFrequency])) {
            return '';
        }

        return $freq[$calFrequency];
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
        // first migrate from tx_cal_category to sys_category
        $variables = [
            'table' => 'tx_cal_category',
            'dbQueries' => $dbQueries,
            'calIds' => $calIds,
        ];

        $db = HelperUtility::getDatabaseConnection($variables['table']);
        $q = $db->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $q->select('*')
            ->from($variables['table']);

        $dbQueries[] = $q->getSQL();

        $selectResults = $q->execute()->fetchAll();

        foreach ($selectResults as $category) {
            $sysCategoryRecord = [
                'pid' => $category['pid'],
                'tstamp' => $category['tstamp'],
                'crdate' => $category['crdate'],
                'cruser_id' => $category['cruser_id'],
                'deleted' => $category['deleted'],
                'hidden' => $category['hidden'],
                'starttime' => $category['starttime'],
                'endtime' => $category['endtime'],
                'sys_language_uid' => $category['sys_language_uid'],
                'l10n_parent' => $category['l18n_parent'],
                'l10n_diffsource' => $category['l18n_diffsource'],
                'title' => $category['title'],
                'parent' => (int)$category['parent_category'],
                'import_id' => self::IMPORT_PREFIX . (int)$category['uid'],
                'sorting' => $category['sorting'],
            ];

            $q->resetQueryParts()->resetRestrictions();

            $q->insert('sys_category')->values($sysCategoryRecord);
            $dbQueries[] = $q->getSQL();

            $q->execute();
        }

        // second rewrite the tree
        $variables = [
            'table' => 'sys_category',
            'dbQueries' => $dbQueries,
            'calIds' => $calIds,
        ];

        $q->resetQueryParts()->resetRestrictions();

        $q->select('*')
            ->from($variables['table'])
            ->where(
                $q->expr()->neq('import_id', $q->createNamedParameter(''))
            );

        $dbQueries[] = $q->getSQL();
        $selectResults = $q->execute()->fetchAll();

        foreach ($selectResults as $sysCategory) {
            // update parent, because there are just the old uids
            $updateRecord = [
                'parent' => $this->getSysCategoryParentUid(self::IMPORT_PREFIX . (int)$sysCategory['parent']),
            ];

            $q->resetQueryParts()->resetRestrictions();
            $q->update('sys_category')
                ->where(
                    $q->expr()->eq('uid', $q->createNamedParameter((int)$sysCategory['uid'], \PDO::PARAM_INT))
                )
                ->values($updateRecord);

            $dbQueries[] = $q->getSQL();

            $q->execute();
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
        $table = 'sys_category';
        $db = HelperUtility::getDatabaseConnection($table);
        $q = $db->createQueryBuilder();

        $q->select('uid')
            ->from($table)
            ->where(
                $q->expr()->eq('import_id', $q->createNamedParameter($importId))
            );

        $dbQueries[] = $q->getSQL();

        $result = $q->execute()->fetchAll();

        return (int)$result['uid'];
    }

    /**
     * Get the event uid on base of the given import_id.
     * The import_id is the original tx_cal_event id prefixed with the IMPORT_PREFIX.
     *
     * @param string $importId
     * @param array  $dbQueries
     * @param array  $customMessages
     *
     * @return int
     */
    protected function getCalendarizeEventUid($importId, &$dbQueries, &$customMessages)
    {
        $variables = [
            'table' => self::EVENT_TABLE,
            'dbQueries' => $dbQueries,
        ];

        $q = HelperUtility::getDatabaseConnection($variables['table'])->createQueryBuilder();

        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        $q->select('uid')
            ->from($variables['table'])
            ->where(
                $q->expr()->eq('import_id', $q->createNamedParameter($importId))
            );

        $dbQueries[] = $q->getSQL();

        $result = $q->execute()->fetchAll();
        $uid = (int)$result['uid'];

        return $uid;
    }

    /**
     * Get the sys_category uid on base of the given import_id.
     * The import_id is the original tx_cal_category id prefixed with the IMPORT_PREFIX.
     *
     * @see CalMigrationUpdate::IMPORT_PREFIX
     *
     * @param string $importId
     * @param array  $dbQueries
     * @param array  $customMessages
     *
     * @return int
     */
    protected function getCalendarizeCategoryUid($importId, &$dbQueries, &$customMessages)
    {
        $variables = [
            'table' => 'sys_category',
            'dbQueries' => $dbQueries,
        ];

        $q = HelperUtility::getDatabaseConnection($variables['table'])->createQueryBuilder();

        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        $q->select('uid')
            ->from($variables['table'])
            ->where(
                $q->expr()->eq('import_id', $q->createNamedParameter($importId))
            );

        $dbQueries[] = $q->getSQL();

        $result = $q->execute()->fetchAll();
        $uid = (int)$result['uid'];

        return $uid;
    }

    /**
     * Build configurations.
     *
     * @param $calEventRow
     * @param $dbQueries
     *
     * @return int
     */
    protected function buildConfigurations($calEventRow, &$dbQueries)
    {
        $configurationRow = [
            'pid' => $calEventRow['pid'],
            'tstamp' => $calEventRow['tstamp'],
            'crdate' => $calEventRow['crdate'],
            'type' => 'time',
            'handling' => 'include',
            'start_date' => $this->migrateDate($calEventRow['start_date']),
            'end_date' => $this->migrateDate($calEventRow['end_date']),
            'start_time' => $calEventRow['start_time'],
            'end_time' => $calEventRow['end_time'],
            'all_day' => $calEventRow['allday'],
            'frequency' => $this->mapFrequency($calEventRow['freq']),
            'till_date' => $this->migrateDate($calEventRow['until']),
            'counter_amount' => (int)$calEventRow['cnt'],
            'counter_interval' => (int)$calEventRow['interval'],
        ];

        $variables = [
            'table' => self::CONFIGURATION_TABLE,
            'configurationRow' => $configurationRow,
            'calEventRow' => $calEventRow,
            'dbQueries' => $dbQueries,
        ];

        $db = HelperUtility::getDatabaseConnection($variables['table']);
        $q = $db->createQueryBuilder();

        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PreInsert', $variables);

        $q->insert($variables['table'])
            ->values($variables['configurationRow']);

        $dbQueries[] = $q->getSQL();
        $q->execute();
        $recordId = $db->lastInsertId($variables['table']);

        $variables = [
            'table' => $variables['table'],
            'configurationRow' => $configurationRow,
            'calEventRow' => $calEventRow,
            'recordId' => $recordId,
            'dbQueries' => $dbQueries,
        ];

        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PostInsert', $variables);

        return $variables['recordId'];
    }

    /**
     * Migrate date.
     *
     * @param $oldFormat
     *
     * @return int|string
     */
    protected function migrateDate($oldFormat)
    {
        try {
            $date = new \DateTime((string)$oldFormat);

            return $date->getTimestamp();
        } catch (\Exception $e) {
        }

        return '';
    }

    /**
     * Get the non migrated cal IDs.
     *
     * @return array
     */
    protected function getNonMigratedCalIds()
    {
        if (!ExtensionManagementUtility::isLoaded('cal')) {
            return [];
        }

        $checkImportIds = [];
        $nonMigrated = [];

        $table = 'tx_cal_event';
        $q = HelperUtility::getDatabaseConnection($table)->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $events = $q->select('uid')
            ->from($table)
            ->execute()
            ->fetchAll();

        foreach ($events as $event) {
            $checkImportIds[] = '"' . self::IMPORT_PREFIX . $event['uid'] . '"';
            $nonMigrated[(int)$event['uid']] = (int)$event['uid'];
        }

        $countOriginal = \count($checkImportIds);
        if (0 === $countOriginal) {
            return [];
        }

        $variables = [
            'table' => self::EVENT_TABLE,
        ];

        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PreSelect', $variables);

        $q->resetQueryParts();

        $migratedRows = $q->select('uid', 'import_id')
            ->from($variables['table'])
            ->where(
                $q->expr()->in('import_id', $checkImportIds)
            );

        foreach ($migratedRows as $migratedRow) {
            $importId = (int)\str_replace(self::IMPORT_PREFIX, '', $migratedRow['import_id']);
            if (isset($nonMigrated[$importId])) {
                unset($nonMigrated[$importId]);
            }
        }

        $variables = [
            'table' => $variables['table'],
            'migratedRows' => $migratedRows,
            'nonMigrated' => $nonMigrated,
        ];

        $dispatcher = HelperUtility::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'ReadyParsed', $variables);

        return $variables['nonMigrated'];
    }

    public function getIdentifier(): string
    {
        return self::class;
    }

    public function getTitle(): string
    {
        return '';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function updateNecessary(): bool
    {
        return false;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }
}
