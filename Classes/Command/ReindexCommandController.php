<?php
/**
 * Reindex the event models
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Service\IndexerService;

/**
 * Reindex the event models
 */
class ReindexCommandController extends AbstractCommandController
{

    /**
     * Run the reindex process
     *
     * @return void
     */
    public function runCommand()
    {
        $indexer = $this->objectManager->get(IndexerService::class);
        $indexer->reindexAll();
    }
}
