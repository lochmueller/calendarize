<?php
/**
 * Index the given events
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Service
 * @author     Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Register;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Index the given events
 *
 * @package    Calendarize
 * @subpackage Service
 * @author     Tim Lochmüller
 */
class IndexerService {

	/**
	 * Reindex the given element
	 *
	 * @param string $configurationKey
	 * @param string $tableName
	 * @param int    $uid
	 *
	 * @return void
	 */
	static public function reindex($configurationKey, $tableName, $uid) {
		/** @var \HDNET\Calendarize\Service\IndexerService $indexer */
		$indexer = GeneralUtility::makeInstance('HDNET\\Calendarize\\Service\\IndexerService');
		$indexer->reindexInternal($configurationKey, $tableName, $uid);
	}

	/**
	 * Reindex the given element / internal function
	 *
	 * @param string $configurationKey
	 * @param string $tableName
	 * @param int    $uid
	 *
	 * @return void
	 */
	public function reindexInternal($configurationKey, $tableName, $uid) {
		$this->cleanupOldIndex($tableName);
		$this->clearIndex($tableName, $uid);
		$this->buildIndex($configurationKey, $tableName, $uid);
	}

	/**
	 * Build the index for one element
	 *
	 * @param string $configurationKey
	 * @param        $tableName
	 * @param        $uid
	 *
	 * @todo iteration
	 * @todo realurl handling of the UID
	 *
	 * @return void
	 */
	protected function buildIndex($configurationKey, $tableName, $uid) {
		$record = BackendUtility::getRecord($tableName, $uid);
		$configurations = GeneralUtility::intExplode(',', $record['calendarize'], TRUE);
		if (!$configurations) {
			return;
		}
		$timeTableService = new TimeTableService();
		$records = $timeTableService->getTimeTablesByConfigurationIds($configurations);
		foreach ($records as $record) {

			$record['foreign_table'] = $tableName;
			$record['foreign_uid'] = $uid;
			$record['unique_register_key'] = $configurationKey;

			$this->prepareRecordForDatabase($record);
			$this->getDatabaseConnection()
				->exec_INSERTquery('tx_calendarize_domain_model_index', $record);
		}
	}

	/**
	 * Prepare the record for the database insert
	 *
	 * @param $record
	 *
	 * @return void
	 */
	protected function prepareRecordForDatabase(&$record) {
		foreach ($record as $key => $value) {
			if ($value instanceof \DateTime) {
				$record[$key] = $value->getTimestamp();
			} elseif (is_bool($value)) {
				$record[$key] = (int)$value;
			} elseif ($value === NULL) {
				$record[$key] = '';
			}
		}
	}

	/**
	 * Reindex all elements
	 *
	 * @return void
	 */
	public function reindexAll() {
		foreach (Register::getRegister() as $key => $configuration) {
			$tableName = $configuration['tableName'];
			$this->cleanupOldIndex($tableName);
			$rows = $this->getDatabaseConnection()
				->exec_SELECTgetRows('uid', $tableName, '1=1' . BackendUtility::deleteClause($tableName));
			foreach ($rows as $row) {
				$this->buildIndex($key, $configuration['tableName'], $row['uid']);
			}
		}
	}

	/**
	 * Cleanup the old index for the given table
	 *
	 * @param $tableName
	 *
	 * @return void
	 */
	protected function cleanupOldIndex($tableName) {
		// @todo check TCA , check deleted flag, search for deleted items and remove the index!!
	}

	/**
	 * Clear the index for one element
	 *
	 * @param $tableName
	 * @param $uid
	 *
	 * @return void
	 */
	protected function clearIndex($tableName, $uid) {
		$this->getDatabaseConnection()
			->exec_DELETEquery('tx_calendarize_domain_model_index', 'foreign_table="' . $tableName . '" AND foreign_uid="' . $uid . '"');
	}

	/**
	 * Get the database connection
	 *
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

}