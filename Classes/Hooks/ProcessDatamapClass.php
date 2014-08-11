<?php
/**
 * Hook for Datamap processing
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Hooks
 * @author     Tim Lochmüller
 */

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Service\IndexerService;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Hook for Datamap processing
 *
 * @package    Calendarize
 * @subpackage Hooks
 * @author     Tim Lochmüller
 * @hook       TYPO3_CONF_VARS|SC_OPTIONS|t3lib/class.t3lib_tcemain.php|processDatamapClass
 */
class ProcessDatamapClass {

	/**
	 * Hook into the after database operations
	 *
	 * @param             $status
	 * @param             $table
	 * @param             $identifier
	 * @param             $fieldArray
	 * @param DataHandler $dataHandler
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $identifier, $fieldArray, DataHandler $dataHandler) {
		$register = Register::getRegister();
		foreach ($register as $key => $configuration) {
			if ($configuration['tableName'] == $table) {
				IndexerService::reindex($key, $table, $identifier);
			}
		}
	}
}
 