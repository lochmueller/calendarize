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
	 * @param string $tableName
	 * @param int    $uid
	 */
	static public function reindex($tableName, $uid) {
		/** @var \HDNET\Calendarize\Service\IndexerService $indexer */
		$indexer = GeneralUtility::makeInstance('HDNET\\Calendarize\\Service\\IndexerService');
		$indexer->reindexInternal($tableName, $uid);
	}

	/**
	 * Reindex the given element / internal function
	 *
	 * @param string $tableName
	 * @param int    $uid
	 */
	public function reindexInternal($tableName, $uid) {
		// Check info

		// check record

		// delete old index

		// rebuild new index
	}

	/**
	 * Reindex all elements
	 */
	public function reindexAll() {

	}

}
 