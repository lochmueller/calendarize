<?php
/**
 * Reindex the event models
 *
 * @package Calendarize\Command
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Command;

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Reindex the event models
 *
 * @author Tim Lochmüller
 */
class ReindexCommandController extends CommandController {

	/**
	 * Run the reindex process
	 *
	 * @return void
	 */
	public function runCommand() {
		/** @var \HDNET\Calendarize\Service\IndexerService $indexer */
		$indexer = $this->objectManager->get('HDNET\\Calendarize\\Service\\IndexerService');
		$indexer->reindexAll();
	}

}