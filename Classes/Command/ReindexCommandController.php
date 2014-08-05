<?php
/**
 * Reindex the event models
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Command
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Command;

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Reindex the event models
 *
 * @package    Calendarize
 * @subpackage Command
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class ReindexCommandController extends CommandController {

	/**
	 * Run the reindex process
	 */
	public function runCommand() {
		/** @var \HDNET\Calendarize\Service\IndexerService $indexer */
		$indexer = $this->objectManager->get('HDNET\\Calendarize\\Service\\IndexerService');
		$indexer->reindexAll();
	}

}
 