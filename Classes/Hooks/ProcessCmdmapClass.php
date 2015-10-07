<?php
/**
 * Hook for cmd map processing
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook for cmd map processing
 *
 * @hook TYPO3_CONF_VARS|SC_OPTIONS|t3lib/class.t3lib_tcemain.php|processCmdmapClass
 */
class ProcessCmdmapClass
{

    /**
     * Run the delete action
     *
     * @param string      $table
     * @param int         $id
     * @param             $recordToDelete
     * @param             $recordWasDeleted
     * @param DataHandler $dataHandler
     */
    public function processCmdmap_deleteAction($table, $id, $recordToDelete, &$recordWasDeleted, DataHandler $dataHandler)
    {
        $register = Register::getRegister();
        foreach ($register as $key => $configuration) {
            if ($configuration['tableName'] == $table) {
                $indexer = GeneralUtility::makeInstance('HDNET\\Calendarize\\Service\\IndexerService');
                $dataHandler->deleteEl($table, $id);
                $recordWasDeleted = true;
                $indexer->reindex($key, $table, $id);
            }
        }
    }
}