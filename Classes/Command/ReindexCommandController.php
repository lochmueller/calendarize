<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Command;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class ReindexCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

	/**
	 * Run the reindex process
	 */
	public function runCommand() {
		/** @var \HDNET\Calendarize\Service\IndexerService $indexer */
		$indexer = $this->objectManager->get('HDNET\\Calendarize\\Service\\IndexerService');
		$indexer->reindexAll();
	}

}
 