<?php
/**
 * Index the given events
 *
 * @package Calendarize\Service
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Register;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Index the given events
 *
 * @author Tim Lochmüller
 */
class IndexerService extends AbstractService {

	/**
	 * Reindex all elements
	 *
	 * @return void
	 */
	public function reindexAll() {
		$this->removeInvalidConfigurationIndex();

		foreach (Register::getRegister() as $key => $configuration) {
			$tableName = $configuration['tableName'];
			$this->removeInvalidRecordIndex($tableName);
			$rows = $this->getDatabaseConnection()
				->exec_SELECTgetRows('uid', $tableName, '1=1' . BackendUtility::deleteClause($tableName));
			foreach ($rows as $row) {
				$this->updateIndex($key, $configuration['tableName'], $row['uid']);
			}
		}
	}

	/**
	 * Reindex the given element
	 *
	 * @param string $configurationKey
	 * @param string $tableName
	 * @param int    $uid
	 *
	 * @return void
	 */
	public function reindex($configurationKey, $tableName, $uid) {
		$this->removeInvalidConfigurationIndex();
		$this->removeInvalidRecordIndex($tableName);
		$this->updateIndex($configurationKey, $tableName, $uid);
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
	protected function updateIndex($configurationKey, $tableName, $uid) {
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
	 * Remove Index items of the given table of records
	 * that are deleted or do not exists anymore.
	 *
	 * @param string $tableName
	 */
	protected function removeInvalidRecordIndex($tableName) {
		$databaseConnection = $this->getDatabaseConnection();
		$rows = $databaseConnection->exec_SELECTgetRows('uid', $tableName, '1=1' . BackendUtility::deleteClause($tableName));
		$ids = array();
		foreach ($rows as $row) {
			$ids[] = $row['uid'];
		}
		if ($ids) {
			$where = 'foreign_table = "' . $tableName . '" AND foreign_uid NOT IN (' . implode(',', $ids) . ')';
		} else {
			$where = 'foreign_table = "' . $tableName . '"';
		}
		$databaseConnection->exec_DELETEquery('tx_calendarize_domain_model_index', $where);
	}

	/**
	 * Remove index Items of configurations that are not valid anymore
	 */
	protected function removeInvalidConfigurationIndex() {
		$validKeys = array_keys(Register::getRegister());
		if ($validKeys) {
			$this->getDatabaseConnection()
				->exec_DELETEquery('tx_calendarize_domain_model_index', 'unique_register_key NOT IN ("' . implode('","', $validKeys) . '")');
		} else {
			$this->getDatabaseConnection()
				->exec_TRUNCATEquery('tx_calendarize_domain_model_index');
		}
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