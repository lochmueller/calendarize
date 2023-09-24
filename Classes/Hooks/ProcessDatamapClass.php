<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Register;
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
     * Hook into the after database operations.
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        int|string $identifier,
        array $fieldArray,
        DataHandler $dataHandler
    ): void {
        $register = Register::getRegister();
        foreach ($register as $configuration) {
            if ($configuration['tableName'] === $table) {
                if ('new' === $status && isset($dataHandler->substNEWwithIDs[$identifier])) {
                    $identifier = $dataHandler->substNEWwithIDs[$identifier];
                }
                $this->indexItems[$table][] = $identifier;
            }
        }
    }

    /**
     * Process the reindex after all operations.
     */
    public function processDatamap_afterAllOperations(DataHandler $dataHandler): void
    {
        if (!$this->indexItems) {
            return;
        }
        $register = Register::getRegister();

        /** @var IndexerService $indexer */
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        foreach ($register as $key => $configuration) {
            foreach ($this->indexItems as $table => $identifiers) {
                if ($table === $configuration['tableName']) {
                    foreach ($identifiers as $uid) {
                        $indexer->reindex($key, $table, (int)$uid);
                    }
                }
            }
        }
        $this->indexItems = [];
    }
}
