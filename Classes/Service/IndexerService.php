<?php
/**
 * Index the given events
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Service
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Index the given events
 *
 * @package    Calendarize
 * @subpackage Service
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class IndexerService {

	/**
	 * Reindex the given element
	 *
	 * @param string $configurationKey
	 * @param string $tableName
	 * @param int    $uid
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
	 */
	public function reindexInternal($configurationKey, $tableName, $uid) {
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
	 */
	protected function buildIndex($configurationKey, $tableName, $uid) {
		$record = BackendUtility::getRecord($tableName, $uid);
		$configurations = GeneralUtility::intExplode(',', $record['calendarize'], TRUE);

		foreach ($configurations as $configurationUid) {
			$records = $this->buildTimeTableByConfigurationUid($configurationUid);
			foreach ($records as $record) {

				$record['foreign_table'] = $tableName;
				$record['foreign_uid'] = $uid;
				$record['unique_register_key'] = $configurationKey;

				$this->prepareRecordForDatabase($record);
				$this->getDatabaseConnection()
					->exec_INSERTquery('tx_calendarize_domain_model_index', $record);
			}
		}
	}

	/**
	 * Prepare the record for the database insert
	 *
	 * @param $record
	 */
	protected function prepareRecordForDatabase(&$record) {
		foreach ($record as $key => $value) {
			if ($value instanceof \DateTime) {
				$record[$key] = $value->format('d-m-Y');
			} elseif (is_bool($value)) {
				$record[$key] = (int)$value;
			} elseif ($value === NULL) {
				$record[$key] = '';
			}
		}

	}

	/**
	 * Get the configuration key by table name
	 *
	 * @param $tableName
	 */
	protected function getConfigurationKeyByTableName($tableName) {

	}

	/**
	 * Build time table by configuration uid
	 *
	 * @param $configurationUid
	 *
	 * @return array
	 */
	protected function buildTimeTableByConfigurationUid($configurationUid) {
		$timeTable = array();

		/** @var \HDNET\Calendarize\Domain\Repository\ConfigurationRepository $configRepository */
		$configRepository = HelperUtility::create('HDNET\\Calendarize\\Domain\\Repository\\ConfigurationRepository');
		$configuration = $configRepository->findByUid($configurationUid);
		if (!($configuration instanceof Configuration)) {
			return $timeTable;
		}

		if ($configuration->getType() == Configuration::TYPE_TIME) {
			$entry = array(
				'start_date' => $configuration->getStartDate(),
				'end_date'   => $configuration->getEndDate(),
				'start_time' => $configuration->getStartTime(),
				'end_time'   => $configuration->getEndTime(),
				'all_day'    => $configuration->getAllDay(),
			);
			$timeTable[] = $entry;
		}

		return $timeTable;
	}

	/**
	 * Reindex all elements
	 *
	 * @todo
	 */
	public function reindexAll() {

	}

	/**
	 * Clear the index for one element
	 *
	 * @param $tableName
	 * @param $uid
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
 