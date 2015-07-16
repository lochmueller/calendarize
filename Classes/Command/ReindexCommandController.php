<?php
/**
 * Reindex the event models
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Command;

/**
 * Reindex the event models
 */
class ReindexCommandController extends AbstractCommandController {

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