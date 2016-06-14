<?php
/**
 * Hook for data map processing
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Service\IndexerService;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook for data map processing
 *
 * @hook   TYPO3_CONF_VARS|SC_OPTIONS|t3lib/class.t3lib_tcemain.php|processDatamapClass
 */
class ProcessDatamapClass extends AbstractHook
{

    /**
     * Index the given items
     *
     * @var array
     */
    protected $indexItems = [];

    /**
     * Hook into the after database operations
     *
     * @param             $status
     * @param             $table
     * @param             $identifier
     * @param             $fieldArray
     * @param DataHandler $dataHandler
     *
     * @return void
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $identifier, $fieldArray, DataHandler $dataHandler)
    {
        $register = Register::getRegister();
        foreach ($register as $configuration) {
            if ($configuration['tableName'] == $table) {
                if ($status == 'new' && isset($dataHandler->substNEWwithIDs[$identifier])) {
                    $identifier = $dataHandler->substNEWwithIDs[$identifier];
                }
                $this->indexItems[$table][] = $identifier;
            }
        }
    }

    /**
     * Process the reindex after all operations
     *
     * @param DataHandler $dataHandler
     *
     * @return void
     */
    public function processDatamap_afterAllOperations(DataHandler $dataHandler)
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
                        $indexer->reindex($key, $table, $uid);
                    }
                }
            }
        }
        $this->indexItems = [];
    }
}
