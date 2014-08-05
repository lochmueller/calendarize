<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo       General class information
 *
 * @package    Calendarize
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class IndexerService {

	/**
	 * @param $tableName
	 * @param $uid
	 */
	static public function reindex($tableName, $uid) {
		/** @var \HDNET\Calendarize\Service\IndexerService $indexer */
		$indexer = GeneralUtility::makeInstance('HDNET\\Calendarize\\Service\\IndexerService');
		$indexer->reindexInternal($tableName, $uid);
	}

	/**
	 * @param $tableName
	 * @param $uid
	 */
	public function reindexInternal($tableName, $uid) {
		// Check info

		// check record

		// delete old index

		// rebuild new index
	}

	public function reindexAll(){

	}

}
 