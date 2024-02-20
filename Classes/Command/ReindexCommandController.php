<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Service\IndexerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Reindex the event models.
 */
class ReindexCommandController extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var IndexerService $indexer */
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();

        return self::SUCCESS;
    }
}
