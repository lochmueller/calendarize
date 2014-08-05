<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Service\IndexerService;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 * @hook       TYPO3_CONF_VARS|SC_OPTIONS|t3lib/class.t3lib_tcemain.php|processDatamapClass
 */
class ProcessDatamapClass {

	/**
	 * @param             $status
	 * @param             $table
	 * @param             $id
	 * @param             $fieldArray
	 * @param DataHandler $dataHandler
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, DataHandler $dataHandler) {
		$register = Register::getRegister();
		foreach ($register as $configuration) {
			if ($configuration['tableName'] == $table) {
				IndexerService::reindex($table, $id);
			}
		}
	}
}
 