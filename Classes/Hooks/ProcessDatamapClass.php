<?php
/**
 * Hook for data map processing
 *
 * @package Calendarize\Hooks
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Hook for data map processing
 *
 * @author Tim Lochmüller
 * @hook   TYPO3_CONF_VARS|SC_OPTIONS|t3lib/class.t3lib_tcemain.php|processDatamapClass
 */
class ProcessDatamapClass {

	/**
	 * Index the given items
	 *
	 * @var array
	 */
	protected $indexItems = array();

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
	public function processDatamap_afterDatabaseOperations($status, $table, $identifier, $fieldArray, DataHandler $dataHandler) {
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
	public function processDatamap_afterAllOperations(DataHandler $dataHandler) {
		if (!$this->indexItems) {
			return;
		}
		$register = Register::getRegister();
		/** @var \HDNET\Calendarize\Service\IndexerService $indexer */
		$indexer = HelperUtility::create('HDNET\\Calendarize\\Service\\IndexerService');
		foreach ($register as $key => $configuration) {
			foreach ($this->indexItems as $table => $identifiers) {
				if ($table === $configuration['tableName']) {
					foreach ($identifiers as $uid) {
						$indexer->reindex($key, $table, $uid);
					}
				}
			}
		}
		$this->indexItems = array();
	}

}