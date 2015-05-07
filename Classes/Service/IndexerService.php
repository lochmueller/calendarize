<?php
/**
 * Index the given events
 *
 * @package Calendarize\Service
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Index the given events
 *
 * @author Tim Lochmüller
 */
class IndexerService extends AbstractService {

	/**
	 * Index table name
	 */
	const TABLE_NAME = 'tx_calendarize_domain_model_index';

	/**
	 * Reindex all elements
	 *
	 * @return void
	 */
	public function reindexAll() {
		$this->removeInvalidConfigurationIndex();
		$databaseConnection = HelperUtility::getDatabaseConnection();

		foreach (Register::getRegister() as $key => $configuration) {
			$tableName = $configuration['tableName'];
			$this->removeInvalidRecordIndex($tableName);
			$rows = $databaseConnection->exec_SELECTgetRows('uid', $tableName, '1=1' . BackendUtility::deleteClause($tableName));
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
	 * @param string $tableName
	 * @param int    $uid
	 *
	 * @return void
	 */
	protected function updateIndex($configurationKey, $tableName, $uid) {
		$rawRecord = BackendUtility::getRecord($tableName, $uid);
		if (!$rawRecord) {
			return;
		}
		$configurations = GeneralUtility::intExplode(',', $rawRecord['calendarize'], TRUE);
		$neededItems = array();
		if ($configurations) {
			$timeTableService = new TimeTableService();
			$neededItems = $timeTableService->getTimeTablesByConfigurationIds($configurations);
			foreach ($neededItems as $key => $record) {

				$record['foreign_table'] = $tableName;
				$record['foreign_uid'] = $uid;
				$record['unique_register_key'] = $configurationKey;

				$this->prepareRecordForDatabase($record);
				$neededItems[$key] = $record;
			}
		}

		$this->addEnableFieldInformation($neededItems, $tableName, $rawRecord);
		$this->insertAndUpdateNeededItems($neededItems, $tableName, $uid);
	}

	/**
	 * Insert and/or update the needed index records
	 *
	 * @param array  $neededItems
	 * @param string $tableName
	 * @param int    $uid
	 */
	protected function insertAndUpdateNeededItems(array $neededItems, $tableName, $uid) {
		$databaseConnection = HelperUtility::getDatabaseConnection();
		$checkProperties = array(
			'start_date',
			'end_date',
			'start_time',
			'end_time',
			'all_day'
		);
		$currentItems = $databaseConnection->exec_SELECTgetRows('uid,' . implode(',', $checkProperties), self::TABLE_NAME, 'foreign_table=' . $databaseConnection->fullQuoteStr($tableName, IndexerService::TABLE_NAME) . ' AND foreign_uid=' . $uid);
		foreach ($neededItems as $neededKey => $neededItem) {
			$remove = FALSE;
			foreach ($currentItems as $currentKey => $currentItem) {
				if ($this->isEqualArray($neededItem, $currentItem, $checkProperties)) {
					$remove = TRUE;
					unset($neededItems[$neededKey]);
					unset($currentItems[$currentKey]);
					break;
				}
			}
			if ($remove) {
				continue;
			}
		}
		foreach ($currentItems as $item) {
			$databaseConnection->exec_DELETEquery(self::TABLE_NAME, 'uid=' . $item['uid']);
		}
		foreach ($neededItems as $item) {
			$databaseConnection->exec_INSERTquery(self::TABLE_NAME, $item);
		}
	}

	/**
	 * Check if the properties of the given arrays are equals
	 *
	 * @param array $array1
	 * @param array $array2
	 * @param array $checkProperties
	 *
	 * @return bool
	 */
	protected function isEqualArray(array $array1, array $array2, array $checkProperties) {
		foreach ($checkProperties as $check) {
			// no type check, because there is also the fe_group field
			if ($array1[$check] != $array2[$check]) {
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * @param array  $neededItems
	 * @param string $tableName
	 * @param array  $record
	 */
	protected function addEnableFieldInformation(array &$neededItems, $tableName, array $record) {
		$enableFields = isset($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']) ? $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns'] : array();
		if (!$enableFields) {
			return;
		}

		$addFields = array();
		if (isset($enableFields['disabled'])) {
			$addFields['hidden'] = (int)$record[$enableFields['disabled']];
		}
		if (isset($enableFields['starttime'])) {
			$addFields['starttime'] = (int)$record[$enableFields['starttime']];
		}
		if (isset($enableFields['endtime'])) {
			$addFields['endtime'] = (int)$record[$enableFields['endtime']];
		}
		if (isset($enableFields['fe_group'])) {
			$addFields['fe_group'] = (string)$record[$enableFields['fe_group']];
		}

		foreach ($neededItems as $key => $value) {
			$neededItems[$key] = array_merge($value, $addFields);
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
		$databaseConnection = HelperUtility::getDatabaseConnection();
		$rows = $databaseConnection->exec_SELECTgetRows('uid', $tableName, '1=1' . BackendUtility::deleteClause($tableName));
		$ids = array();
		foreach ($rows as $row) {
			$ids[] = $row['uid'];
		}
		$where = 'foreign_table=' . $databaseConnection->fullQuoteStr($tableName, IndexerService::TABLE_NAME);
		if ($ids) {
			$where .= ' AND foreign_uid NOT IN (' . implode(',', $ids) . ')';
		}
		$databaseConnection->exec_DELETEquery(self::TABLE_NAME, $where);
	}

	/**
	 * Remove index Items of configurations that are not valid anymore
	 *
	 * @return bool
	 */
	protected function removeInvalidConfigurationIndex() {
		$validKeys = array_keys(Register::getRegister());
		$databaseConnection = HelperUtility::getDatabaseConnection();
		if ($validKeys) {
			foreach ($validKeys as $key => $value) {
				$validKeys[$key] = $databaseConnection->fullQuoteStr($value, IndexerService::TABLE_NAME);
			}
			return (bool)$databaseConnection->exec_DELETEquery(self::TABLE_NAME, 'unique_register_key NOT IN (' . implode(',', $validKeys) . ')');
		}
		return (bool)$databaseConnection->exec_TRUNCATEquery(self::TABLE_NAME);
	}

}