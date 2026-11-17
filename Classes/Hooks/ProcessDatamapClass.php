<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Service\ConfigurationRelationService;
use HDNET\Calendarize\Service\IndexerService;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook for data map processing.
 */
class ProcessDatamapClass
{
    /**
     * Index the given items.
     */
    protected array $indexItems = [];

    /**
     * Changed configurations (and configuration groups), that are used by other records.
     */
    protected array $changedConfigurations = [];

    protected array $changedGroups = [];

    /**
     * Hook into the after database operations.
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        int|string $identifier,
        array $fieldArray,
        DataHandler $dataHandler,
    ): void {
        if ('new' === $status && isset($dataHandler->substNEWwithIDs[$identifier])) {
            $identifier = $dataHandler->substNEWwithIDs[$identifier];
        }

        if (\in_array($table, array_column(Register::getRegister(), 'tableName'), true)) {
            $this->indexItems[$table][] = $identifier;
        }

        // Changes of a configuration or a configuration group affect all records that use them
        if (ConfigurationRelationService::CONFIGURATION_TABLE === $table) {
            $this->changedConfigurations[] = (int)$identifier;
        } elseif (ConfigurationRelationService::GROUP_TABLE === $table) {
            $this->changedGroups[] = (int)$identifier;
        }
    }

    /**
     * Process the reindex after all operations.
     */
    public function processDatamap_afterAllOperations(DataHandler $dataHandler): void
    {
        if (!$this->indexItems && !$this->changedConfigurations && !$this->changedGroups) {
            return;
        }
        $configurationByTable = array_column(Register::getRegister(), null, 'tableName');

        $records = [];
        foreach ($this->indexItems as $table => $identifiers) {
            foreach ($identifiers as $uid) {
                $records[$table . ':' . (int)$uid] = [
                    'uniqueRegisterKey' => $configurationByTable[$table]['uniqueRegisterKey'],
                    'tableName' => $table,
                    'uid' => (int)$uid,
                ];
            }
        }

        // Add the records (e.g. events), that use one of the changed configurations/configuration groups
        $relationService = GeneralUtility::makeInstance(ConfigurationRelationService::class);
        $affectedRecords = $relationService->findAffectedRecords($this->changedConfigurations, $this->changedGroups);
        foreach ($affectedRecords as $record) {
            $records[$record['tableName'] . ':' . $record['uid']] = $record;
        }

        /** @var IndexerService $indexer */
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        foreach ($records as $record) {
            $indexer->reindex($record['uniqueRegisterKey'], $record['tableName'], $record['uid']);
        }

        $this->indexItems = [];
        $this->changedConfigurations = [];
        $this->changedGroups = [];
    }
}
