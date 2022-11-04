<?php

/**
 * CalMigrationUpdate.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use HDNET\Autoloader\Annotation\SignalClass;
use HDNET\Autoloader\Annotation\SignalName;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Utility\HelperUtility;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;

/**
 * CalMigrationUpdate.
 *
 * If using the slots please use the m with func_get_args!
 * Example:
 * /**
 *  * @SignalClass("HDNET\Calendarize\Updates\CalMigrationUpdate")
 *  * @SignalName("getCalendarizeEventUid")
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
class CalMigrationUpdate extends AbstractUpdate implements ChattyInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Import prefix.
     */
    public const IMPORT_PREFIX = 'calMigration:';

    /**
     * Event table.
     */
    public const EVENT_TABLE = 'tx_calendarize_domain_model_event';

    /**
     * Configuration table.
     */
    public const CONFIGURATION_TABLE = 'tx_calendarize_domain_model_configuration';

    /**
     * ConfigurationGroup table.
     */
    public const CONFIGURATION_GROUP_TABLE = 'tx_calendarize_domain_model_configurationgroup';

    /**
     * The human-readable title of the upgrade wizard.
     *
     * @var string
     */
    protected $title = 'Migrate cal event structures to the new calendarize event structures.
    Try to migrate all cal information and place the new calendarize event models in the same folder
    as the cal-records. Please note: the migration will be create calendarize default models.';

    public function getIdentifier(): string
    {
        return 'calendarize_calMigration';
    }

    /**
     * @var OutputInterface
     */
    protected $output;

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

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
        if (\PHP_VERSION_ID >= 80000 || GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 10) {
            $this->output->writeln(
                'The Cal migration wizard does not TYPO3 >= 11 and not PHP 8. Please use TYPO3 v10.4!'
            );

            return false;
        }

        $this->logger->debug('Start CalMigration executeUpdate');
        $calIds = $this->getNonMigratedCalIds();
        if (empty($calIds)) {
            $this->output->writeln('No non-migrated cal entries found!');

            return true;
        }
        $this->output->writeln(
            'Start importing cals (count: ' . count($calIds) . ') ...'
        );
        $dbQueries = [];

        $this->logger->debug('Start executeUpdate for cals: ', $calIds);

        /**
         * @var bool
         */
        $calUsesSysCategories = $this->isCalWithSysCategories();

        if (!$calUsesSysCategories) {
            $this->performSysCategoryUpdate($calIds, $dbQueries, $customMessages);
        }
        $this->performSysFileReferenceUpdate($calIds, $dbQueries, $customMessages);
        $this->performExceptionEventUpdate($calIds, $dbQueries, $customMessages);
        $this->performCalEventUpdate($calIds, $dbQueries, $customMessages);
        if ($calUsesSysCategories) {
            $this->performLinkEventToSysCategory($calIds, $dbQueries, $customMessages);
        } else {
            $this->performLinkEventToCategory($calIds, $dbQueries, $customMessages);
        }
        $this->performLinkEventToConfigurationGroup($calIds, $dbQueries, $customMessages);
        $this->finalMessage($calIds);
        return true;
    }

    /**
     * Check if cal is already using sys_category instead
     * of tx_cal_category. This affects the conversion of
     * categories and category / event relations.
     *
     * @return bool
     */
    protected function isCalWithSysCategories(): bool
    {
        $table = 'sys_category_record_mm';

        $q = $this->getQueryBuilder($table);
        $count = $q->count('*')
            ->from($table)
            ->where(
                $q->expr()->eq('tablenames', $q->createNamedParameter('tx_cal_event')),
                $q->expr()->eq('fieldname', $q->createNamedParameter('category_id'))
            )
            ->execute()->fetch();

        return (int)$count['COUNT(*)'] > 0;
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
        $this->logger->debug('Start performCalEventUpdate');
        $table = 'tx_cal_event';
        $q = $this->getQueryBuilder($table);

        $locationTable = 'tx_cal_location';
        $locations = $this->getQueryBuilder($locationTable)
            ->select('*')
            ->from($locationTable)
            ->execute()
            ->fetchAll();
        $locationByUid = [];
        foreach ($locations as $location) {
            $locationByUid[$location['uid']] = $location['name'];
        }

        $events = $q->select('*')
            ->from($table)
            ->where(
                $q->expr()->in('uid', array_map('intval', $calIds))
            )
            ->orderBy('l18n_parent')
            ->execute()->fetchAll();

        foreach ($events as $event) {
            // Get the parent id of the event record
            // Note: The parent record should exist at this time, since we orderBy('l18n_parent')
            $parentEventUid = 0;
            if ($event['l18n_parent']) {
                $parentEventUid = (int)$this->getCalendarizeEventUid(
                    self::IMPORT_PREFIX . $event['l18n_parent'],
                    $dbQueries,
                    $customMessages
                );
            }

            $calendarizeEventRecord = [
                'pid' => $event['pid'],
                'import_id' => self::IMPORT_PREFIX . (int)$event['uid'],
                'sys_language_uid' => $event['sys_language_uid'] ?? 0,
                'l10n_parent' => $parentEventUid,
                'tstamp' => $event['tstamp'],
                'crdate' => $event['crdate'],
                'hidden' => $event['hidden'],
                'starttime' => $event['starttime'],
                'endtime' => $event['endtime'],
                'title' => $event['title'],
                'organizer' => $event['organizer'],
                'location' => $event['location_id'] > 0 ? $locationByUid[$event['location_id']] : $event['location'],
                'abstract' => $event['teaser'],
                'description' => $event['description'],
                'images' => (int)$event['image'],
                'downloads' => (int)$event['attachment'],
                'calendarize' => $this->buildConfigurations($event, $dbQueries),
            ];

            $variables = [
                'calendarizeEventRecord' => $calendarizeEventRecord,
                'event' => $event,
                'table' => self::EVENT_TABLE,
                'dbQueries' => $dbQueries,
            ];

            $dispatcher = self::getSignalSlotDispatcher();
            $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PreInsert', $variables);

            $db = HelperUtility::getDatabaseConnection($variables['table']);
            $q = $db->createQueryBuilder();
            $q->insert($variables['table'])->values($variables['calendarizeEventRecord']);
            $dbQueries[] = HelperUtility::queryWithParams($q);

            $q->execute();

            $variablesPostInsert = [
                'calendarizeEventRecord' => $calendarizeEventRecord,
                'event' => $event,
                'table' => $variables['table'],
                'recordId' => $db->lastInsertId($variables['table']),
                'dbQueries' => $dbQueries,
            ];

            $dispatcher = self::getSignalSlotDispatcher();
            $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PostInsert', $variablesPostInsert);
            $this->logger->debug("after performCalEventUpdatePostInsert: " . $variablesPostInsert['event']['uid'] . " dbQueries: " . print_r($variablesPostInsert['dbQueries'],true));
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
        $this->logger->debug('Start performExceptionEventUpdate');
        $table = 'tx_cal_exception_event_group';
        // ConfigurationGroup für jede ExceptionGroup

        $variables = [
            'table' => $table,
            'dbQueries' => $dbQueries,
            'calIds' => $calIds,
        ];

        $q = $this->getQueryBuilder($table);

        $q->select('*')->from($variables['table']);

        $selectResults = $q->execute()->fetchAll();
        $dbQueries[] = HelperUtility::queryWithParams($q);

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

            $this->output->writeln(
                'Start exception event group ' . $variables['table']
                . ' (import_id ' . $group['import_id'] . '/pid' . $group['pid'] . ') ...'
            );
            $q = $this->getQueryBuilder($table);
            $q->insert(self::CONFIGURATION_GROUP_TABLE)->values($group);
            $dbQueries[] = HelperUtility::queryWithParams($q);

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
        $this->logger->debug('Start performLinkEventToConfigurationGroup');
        $this->output->writeln('Start performLinkEventToConfigurationGroup');
        $q = $this->getQueryBuilder(self::CONFIGURATION_GROUP_TABLE);
        $now = new \DateTime();

        $variables = [
            'table' => self::CONFIGURATION_GROUP_TABLE,
            'dbQueries' => $dbQueries,
            'calIds' => $calIds,
        ];

        $selectResults = $q->select('*')->from($variables['table'])->execute()->fetchAll();
        $dbQueries[] = HelperUtility::queryWithParams($q);

        foreach ($selectResults as $group) {
            $importId = explode(':', $group['import_id']);
            $groupId = (int)$importId[1];

            $variables = [
                'table' => 'tx_cal_exception_event_mm',
                'dbQueries' => $dbQueries,
                'calIds' => $calIds,
            ];

            $q = $this->getQueryBuilder($variables['table']);

            $q->select('uid_local')
                ->from($variables['table'])
                ->where(
                    $q->expr()->andX(
                        $q->expr()->eq('tablenames', $q->createNamedParameter('tx_cal_exception_event_group')),
                        $q->expr()->eq('uid_foreign', $q->createNamedParameter((int)$groupId, \PDO::PARAM_INT))
                    )
                );

            $dbQueries[] = HelperUtility::queryWithParams($q);
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
        $this->logger->debug('Start performSysFileReferenceUpdate');
        $this->output->writeln( 'Start performSysFileReferenceUpdate' );
        $q = $this->getQueryBuilder('tx_cal_event');

        $variables = [
            'table' => 'tx_cal_event',
            'fieldnames' => ['image', 'attachment'],
            'dbQueries' => $dbQueries,
            'calIds' => $calIds,
        ];

        // select all not migrated entries
        $fieldnames = 'fieldname = \'' . implode('\' OR fieldname = \'', $variables['fieldnames']) . '\'';
        $selectWhere = 'tablenames = \'' . $variables['table'] . '\' AND (' . $fieldnames . ')';
        $selectWhere .= ' AND NOT EXISTS (SELECT NULL FROM sys_file_reference sfr2 WHERE sfr2.import_id = CONCAT(\'' . self::IMPORT_PREFIX . '\', sfr1.uid))';

        $q->select('*')
            ->from('sys_file_reference', 'sfr1')
            ->where($selectWhere);

        $dbQueries[] = HelperUtility::queryWithParams($q);
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

            $this->output->writeln(
                'Start migrating ' . $selectResult['tablenames']
                . ' (import_id' . $selectResult['import_id'] . ') ...'
            );

            $q = $this->getQueryBuilder('sys_file_reference');
            $q->insert('sys_file_reference')->values($selectResult);

            $dbQueries[] = HelperUtility::queryWithParams($q);

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
        $this->logger->debug('Start performLinkEventToCategory');
        $table = 'tx_cal_event_category_mm';

        $q = $this->getQueryBuilder($table);

        $q->select('*')->from($table);
        $dbQueries[] = HelperUtility::queryWithParams($q);

        $selectResults = $q->execute()->fetchAll();

        $variables = [
            'tablenames' => self::EVENT_TABLE,
            'fieldname' => 'categories',
            'dbQueries' => $dbQueries,
        ];

        $dispatcher = self::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        foreach ($selectResults as $mm) {
            $eventUid = (int)$this->getCalendarizeEventUid(self::IMPORT_PREFIX . $mm['uid_local'], $dbQueries, $customMessages);
            $categoryUid = (int)$this->getCalendarizeCategoryUid(
                self::IMPORT_PREFIX . $mm['uid_foreign'],
                $dbQueries,
                $customMessages
            );

            if (0 !== $eventUid && 0 !== $categoryUid) {
                $insertValues = [
                    'uid_local' => $categoryUid,
                    'uid_foreign' => $eventUid,
                    'tablenames' => $variables['tablenames'],
                    'fieldname' => $variables['fieldname'],
                ];

                $q = $this->getQueryBuilder($table);
                $q->insert($table)->values($insertValues);
                $dbQueries[] = HelperUtility::queryWithParams($q);

                $q->execute();
            }
        }
    }

    /**
     * Link the Events to the migrated categories.
     *
     * This uses the existing 'sys_category_record_mm' table which links tx_cal_event to sys_category.
     * The fields must be updated to use tx_calendarize_domain_model_event instead.
     * Additionally, the uid_foreign must be updated to point to the new event uid.
     *
     * Before: tablenames='tx_cal_event', fieldname='category_id'
     * After: tablenames='tx_calendarize_domain_model_event', fieldname='categories'
     *
     * @param       $calIds
     * @param array $dbQueries
     * @param array $customMessages
     */
    public function performLinkEventToSysCategory($calIds, &$dbQueries, &$customMessages)
    {
        $this->logger->debug('Start performLinkEventToSysCategory');
        $this->output->writeln(
            'Start link events to syscategory (count: ' . count($calIds) . ') ...'
        );
        $table = 'sys_category_record_mm';

        $q = $this->getQueryBuilder($table);

        $q->select('uid_foreign')->from($table)
            ->where(
                $q->expr()->eq('tablenames', $q->createNamedParameter('tx_cal_event')),
                $q->expr()->eq('fieldname', $q->createNamedParameter('category_id')),
                $q->expr()->neq('uid_local', $q->createNamedParameter(0, \PDO::PARAM_INT)),
                $q->expr()->neq('uid_foreign', $q->createNamedParameter(0, \PDO::PARAM_INT))
            )->groupBy('uid_foreign');

        $selectResults = $q->execute()->fetchAll();

        $variables = [
            'tablenames' => self::EVENT_TABLE,
            'fieldname' => 'categories',
        ];

        $dispatcher = self::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        foreach ($selectResults as $mm) {
            $eventUidOld = (int)$mm['uid_foreign'];
            // event id is in uid_foreign
            $eventUid = (int)$this->getCalendarizeEventUid(self::IMPORT_PREFIX . $eventUidOld, $dbQueries, $customMessages);

            $q = $this->getQueryBuilder($table);
            if (0 !== $eventUid) {
                $q->update($table)
                    ->set('tablenames', $variables['tablenames'])
                    ->set('fieldname', $variables['fieldname'])
                    ->set('uid_foreign', $eventUid)
                    ->where(
                        $q->expr()->eq('uid_foreign', $q->createNamedParameter($eventUidOld, \PDO::PARAM_INT)),
                        $q->expr()->eq('tablenames', $q->createNamedParameter('tx_cal_event')),
                        $q->expr()->eq('fieldname', $q->createNamedParameter('category_id'))
                    )->execute();
            } else {
                // TODO - log deleted 0 eventUid
                $this->logger->debug("In performLinkEventToSyscategory but event[uid] is $eventUid and trying to delete ");
                $this->output->writeln(
                    "In performLinkEventToSyscategory but event[uid] is $eventUid and eventUidOld is $eventUidOld trying to delete foreign tx_cal_event"
                );
                $q->delete($table)
                    ->where(
                        $q->expr()->eq('uid_foreign', $q->createNamedParameter($eventUid, \PDO::PARAM_INT)),
                        $q->expr()->eq('tablenames', $q->createNamedParameter('tx_cal_event')),
                        $q->expr()->eq('fieldname', $q->createNamedParameter('category_id'))
                    )
                    ->execute();
            }
        }

        // delete remaining entries with insufficient values (e.g. uid_foreign=0)
        $q = $this->getQueryBuilder($table);
        $q->delete($table)
            ->where(
                $q->expr()->eq('tablenames', $q->createNamedParameter('tx_cal_event')),
                $q->expr()->orX(
                    $q->expr()->eq('fieldname', $q->createNamedParameter('')),
                    $q->expr()->eq('uid_local', $q->createNamedParameter(0, \PDO::PARAM_INT)),
                    $q->expr()->eq('uid_foreign', $q->createNamedParameter(0, \PDO::PARAM_INT))
                )
            )->execute();
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
        $configurationRow = $this->findEventExcludeConfiguration($eventImportId, $dbQueries, $customMessages);
        if ($configurationRow) {
            $configurationRow['groups'] = $this->addValueToCsv($configurationRow['groups'], $configuration['groups']);

            unset($configurationRow['uid']);

            $q = $this->getQueryBuilder(self::CONFIGURATION_GROUP_TABLE);
            $q->update(self::CONFIGURATION_GROUP_TABLE)
                ->where('uid', $q->createNamedParameter((int)$configuration['uid'], \PDO::PARAM_INT));
            foreach ($configurationRow as $key => $value) {
                $q->set($key, $value);
            }

            $dbQueries[] = HelperUtility::queryWithParams($q);
            $results = $q->execute();
        } else {
            $db = HelperUtility::getDatabaseConnection(self::CONFIGURATION_TABLE);
            $q = $db->createQueryBuilder();
            $q->insert(self::CONFIGURATION_TABLE)->values($configuration);
            $dbQueries[] = HelperUtility::queryWithParams($q);

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
        $values = array_flip($csvArray);
        if (isset($values[$value])) {
            return $csv;
        }
        $csvArray[] = $value;
        $csv = implode(',', $csvArray);

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
        $q = $this->getQueryBuilder(self::EVENT_TABLE);

        $variables = [
            'table' => self::EVENT_TABLE,
            'eventId' => (int)$eventId,
            'values' => $values,
            'dbQueries' => $dbQueries,
        ];

        $dispatcher = self::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        $q->update($variables['table'])
            ->where(
                $q->expr()->eq('uid', $q->createNamedParameter((int)$eventId, \PDO::PARAM_INT))
            );
        foreach ($variables['values'] as $key => $value) {
            $q->set($key, $value);
        }

        unset($values['uid']);

        $dbQueries[] = HelperUtility::queryWithParams($q);

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
        $q = $this->getQueryBuilder(self::EVENT_TABLE);

        $variables = [
            'table' => self::EVENT_TABLE,
            'dbQueries' => $dbQueries,
            'eventImportId' => $eventImportId,
        ];

        $dispatcher = self::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        $q->select('*')->from($variables['table'])
            ->where(
                $q->expr()->eq('import_id', $q->createNamedParameter($eventImportId))
            );

        $dbQueries[] = HelperUtility::queryWithParams($q);

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

        $q = $this->getQueryBuilder($variables['table']);

        $q->select('*')
            ->from($variables['table'])
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('type', 'group'),
                    $q->expr()->eq('handling', 'exclude'),
                    $q->expr()->in('uid', $variables['event']['calendarize'])
                )
            );

        $dbQueries[] = HelperUtility::queryWithParams($q);

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
        $this->logger->debug('Start getExceptionConfigurationForExceptionGroup');
        $recordIds = [];
        $variables = [
            'table' => 'tx_cal_exception_event_group_mm',
            'dbQueries' => $dbQueries,
        ];

        $q = $this->getQueryBuilder($variables['table']);

        $q->select('*')
            ->from($variables['table'])
            ->where('uid_local', $q->createNamedParameter((int)$groupId, \PDO::PARAM_INT));

        $dbQueries[] = HelperUtility::queryWithParams($q);

        $mmResults = $q->execute()->fetchAll();
        foreach ($mmResults as $mmResult) {
            $variables = [
                'table' => 'tx_cal_exception_event',
                'dbQueries' => $dbQueries,
            ];

            $q = $this->getQueryBuilder($variables['table']);
            $q->select('*')
                ->from($variables['table'])
                ->where(
                    $q->expr()->eq('uid', $q->createNamedParameter((int)$mmResult['uid_foreign'], \PDO::PARAM_INT))
                );

            $dbQueries[] = HelperUtility::queryWithParams($q);

            $selectResults = $q->execute()->fetchAll();

            foreach ($selectResults as $selectResult) {
                $configurationRow = [
                    'pid' => $selectResult['pid'],
                    'tstamp' => $selectResult['tstamp'],
                    'crdate' => $selectResult['crdate'],
                    'type' => 'time',
                    'handling' => 'include',
                    'start_date' => (string)$selectResult['start_date'] ?: null,
                    'end_date' => (string)$selectResult['end_date'] ?: null,
                    'start_time' => (int)$selectResult['start_time'],
                    'end_time' => (int)$selectResult['end_time'],
                    'all_day' => (null === $selectResult['start_time'] && null === $selectResult['end_time']) ? 1 : 0,
                    'frequency' => $this->mapFrequency($selectResult['freq']),
                    'till_date' => (string)$selectResult['until'] ?: null,
                    'counter_amount' => (int)$selectResult['cnt'],
                    'counter_interval' => (int)$selectResult['interval'],
                    'import_id' => self::IMPORT_PREFIX . $selectResult['uid'],
                    'recurrence' => $this->mapRecurrence($selectResult['byday']),
                    'day' => $this->mapRecurrenceDay($selectResult['byday']),
                ];

                $variables = [
                    'table' => self::CONFIGURATION_TABLE,
                    'configurationRow' => $configurationRow,
                ];

                $dispatcher = self::getSignalSlotDispatcher();
                $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PreInsert', $variables);

                $db = HelperUtility::getDatabaseConnection($variables['table']);
                $q = $db->createQueryBuilder();
                $q->insert($variables['table'])->values($variables['configurationRow']);

                $dbQueries[] = HelperUtility::queryWithParams($q);

                $q->execute();

                $recordIds[] = $db->lastInsertId($variables['table']);
            }
        }

        return implode(',', $recordIds);
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

        return $freq[$calFrequency] ?? '';
    }

    /**
     * Map day of recurrence.
     *
     * @param string $calByday
     *
     * @return string
     */
    protected function mapRecurrenceDay($calByday)
    {
        $days = [
            'mo' => ConfigurationInterface::DAY_MONDAY,
            'tu' => ConfigurationInterface::DAY_TUESDAY,
            'we' => ConfigurationInterface::DAY_WEDNESDAY,
            'th' => ConfigurationInterface::DAY_THURSDAY,
            'fr' => ConfigurationInterface::DAY_FRIDAY,
            'sa' => ConfigurationInterface::DAY_SATURDAY,
            'su' => ConfigurationInterface::DAY_SUNDAY,
        ];
        $recurrenceDay = substr($calByday, -2);

        if (empty($calByday) || !array_key_exists($recurrenceDay, $days)) {
            return '';
        }

        return $days[$recurrenceDay];
    }

    /**
     * Map day of recurrence.
     *
     * @param string $calByday
     *
     * @return string
     */
    protected function mapRecurrence($calByday)
    {
        $recurrences = [
            '1' => ConfigurationInterface::RECURRENCE_FIRST,
            '2' => ConfigurationInterface::RECURRENCE_SECOND,
            '3' => ConfigurationInterface::RECURRENCE_THIRD,
            '4' => ConfigurationInterface::RECURRENCE_FOURTH,
            '5' => ConfigurationInterface::RECURRENCE_FIFTH,
            '-1' => ConfigurationInterface::RECURRENCE_LAST,
            '-2' => ConfigurationInterface::RECURRENCE_NEXT_TO_LAST,
            '-3' => ConfigurationInterface::RECURRENCE_THIRD_LAST,
        ];
        $recurrence = substr($calByday, 0, -2); // cut last 2 chars
        if (empty($calByday) || !array_key_exists($recurrence,$recurrences)) {
            return '';
        }

        return $recurrences[$recurrence];
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

        $q = $this->getQueryBuilder($variables['table']);
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $q->select('*')
            ->from($variables['table']);

        $dbQueries[] = HelperUtility::queryWithParams($q);

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
                'title' => $category['title'],
                'parent' => (int)$category['parent_category'],
                'import_id' => self::IMPORT_PREFIX . (int)$category['uid'],
                'sorting' => $category['sorting'],
            ];

            $q = $this->getQueryBuilder('sys_category');
            $q->insert('sys_category')->values($sysCategoryRecord);
            $dbQueries[] = HelperUtility::queryWithParams($q);

            $q->execute();
        }

        // second rewrite the tree
        $variables = [
            'table' => 'sys_category',
            'dbQueries' => $dbQueries,
            'calIds' => $calIds,
        ];

        $q = $this->getQueryBuilder($variables['table']);

        $q->select('*')
            ->from($variables['table'])
            ->where(
                $q->expr()->like('import_id', $q->createNamedParameter(self::IMPORT_PREFIX . '%'))
            );

        $dbQueries[] = HelperUtility::queryWithParams($q);
        $selectResults = $q->execute()->fetchAll();

        foreach ($selectResults as $sysCategory) {
            if (empty($sysCategory['parent'])) {
                // Skip categories without a parent
                continue;
            }
            // update parent, because there are just the old uids
            $q = $this->getQueryBuilder('sys_category');
            $q->update('sys_category')
                ->where(
                    $q->expr()->eq('uid', $q->createNamedParameter((int)$sysCategory['uid'], \PDO::PARAM_INT))
                )->set(
                    'parent',
                    $this->getSysCategoryParentUid(self::IMPORT_PREFIX . (int)$sysCategory['parent'])
                );

            $dbQueries[] = HelperUtility::queryWithParams($q);

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
        $q = $this->getQueryBuilder($table);

        $q->select('uid')
            ->from($table)
            ->where(
                $q->expr()->eq('import_id', $q->createNamedParameter($importId))
            );

        $dbQueries[] = HelperUtility::queryWithParams($q);

        $result = $q->execute()->fetchAll();

        return (int)($result[0]['uid'] ?? 0);
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

        $dispatcher = self::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        $q = $this->getQueryBuilder($variables['table']);

        // also get restricted (e.g. hidden) records, otherwise retrieving event uid for
        // restricted records will always return 0
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $q->select('uid')
            ->from($variables['table'])
            ->where(
                $q->expr()->eq('import_id', $q->createNamedParameter($importId))
            );

        $dbQueries[] = HelperUtility::queryWithParams($q);

        $result = $q->execute()->fetchAll();

        return (int)($result[0]['uid'] ?? 0);
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

        $dispatcher = self::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        $q = $this->getQueryBuilder($variables['table']);

        $q->select('uid')
            ->from($variables['table'])
            ->where(
                $q->expr()->eq('import_id', $q->createNamedParameter($importId))
            );

        $dbQueries[] = HelperUtility::queryWithParams($q);

        $result = $q->execute()->fetchAll();

        return (int)($result[0]['uid'] ?? 0);
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
            'start_date' => (string)$calEventRow['start_date'] ?: null,
            'end_date' => (string)$calEventRow['end_date'] ?: null,
            'start_time' => (int)$calEventRow['start_time'],
            'end_time' => (int)$calEventRow['end_time'],
            'all_day' => $calEventRow['allday'],
            'frequency' => $this->mapFrequency($calEventRow['freq']),
            'till_date' => (string)$calEventRow['until'] ?: null,
            'counter_amount' => (int)$calEventRow['cnt'],
            'counter_interval' => (int)($calEventRow['interval'] ?? 1),
            'recurrence' => $this->mapRecurrence($calEventRow['byday']),
            'day' => $this->mapRecurrenceDay($calEventRow['byday']),
        ];

        $variables = [
            'table' => self::CONFIGURATION_TABLE,
            'configurationRow' => $configurationRow,
            'calEventRow' => $calEventRow,
            'dbQueries' => $dbQueries,
        ];

        $db = HelperUtility::getDatabaseConnection($variables['table']);
        $q = $db->createQueryBuilder();

        $dispatcher = self::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PreInsert', $variables);

        $q->insert($variables['table'])
            ->values($variables['configurationRow']);

        $dbQueries[] = HelperUtility::queryWithParams($q);
        $q->execute();
        $recordId = $db->lastInsertId($variables['table']);

        $variables = [
            'table' => $variables['table'],
            'configurationRow' => $configurationRow,
            'calEventRow' => $calEventRow,
            'recordId' => $recordId,
            'dbQueries' => $dbQueries,
        ];

        $dispatcher = self::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PostInsert', $variables);

        return $variables['recordId'];
    }

    /**
     * Get the non migrated cal IDs.
     *
     * @return array
     */
    protected function getNonMigratedCalIds()
    {
        $checkImportIds = [];
        $nonMigrated = [];

        $table = 'tx_cal_event';
        $q = $this->getQueryBuilder($table);

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

        $dispatcher = self::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'PreSelect', $variables);

        $q = $this->getQueryBuilder($variables['table']);

        $migratedRows = $q->select('uid', 'import_id')
            ->from($variables['table'])
            ->where(
                $q->expr()->in('import_id', $checkImportIds)
            )
            ->execute()
            ->fetchAll();

        foreach ($migratedRows as $migratedRow) {
            $importId = (int)str_replace(self::IMPORT_PREFIX, '', $migratedRow['import_id']);
            if (isset($nonMigrated[$importId])) {
                unset($nonMigrated[$importId]);
            }
        }

        $variables = [
            'table' => $variables['table'],
            'migratedRows' => $migratedRows,
            'nonMigrated' => $nonMigrated,
        ];

        $dispatcher = self::getSignalSlotDispatcher();
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__ . 'ReadyParsed', $variables);

        return $variables['nonMigrated'];
    }

    public function updateNecessary(): bool
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $dbSchema = $connection->getSchemaManager()->createSchema();

        $tableNames = array_map(static function ($table) {
            return $table->getName();
        }, $dbSchema->getTables());

        return \in_array('tx_cal_event', $tableNames);
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    private function getQueryBuilder(string $table): QueryBuilder
    {
        $queryBuilder = HelperUtility::getDatabaseConnection($table)->createQueryBuilder();

        // This adds a custom restriction similar to DeletedRestriction
        // When 'cal' is not installed there is no TCA definition.
        // This causes the default restrictions to not work.
        // This adds a custom restriction to filter out deleted records.
        if (str_starts_with($table, 'tx_cal_')) {
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(new class() implements QueryRestrictionInterface {
                    public function buildExpression(
                        array $queriedTables,
                        ExpressionBuilder $expressionBuilder
                    ): CompositeExpression {
                        $constraints = [];
                        foreach ($queriedTables as $tableAlias => $tableName) {
                            if (str_starts_with($tableName, 'tx_cal_') && !str_ends_with($tableName, '_mm')) {
                                $constraints[] = $expressionBuilder->eq(
                                    $tableAlias . '.deleted',
                                    0
                                );
                            }
                        }

                        return $expressionBuilder->andX(...$constraints);
                    }
                });
        }

        return $queryBuilder;
    }

    /**
     * Get the signal slot dispatcher.
     *
     * @return Dispatcher
     */
    public static function getSignalSlotDispatcher(): Dispatcher
    {
        return GeneralUtility::makeInstance(Dispatcher::class);
    }

    /**
     * @param array $records
     * @return void
     */
    protected function finalMessage(array $records)
    {
        $message = count($records) . ' record(s) in cals. Left cals: ' . count($this->getNonMigratedCalIds());
        $this->output->writeln($message);
        $this->logger->debug($message);
    }
}
