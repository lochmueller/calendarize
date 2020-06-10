<?php

/**
 * Reindex the event models.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Service\IndexerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Reindex the event models.
 */
class ReindexCommandController extends Command
{
    public function configure()
    {
        $this->setDescription('Calendarize: Reindex all events');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws InvalidConfigurationTypeException
     * @throws InvalidExtensionNameException
     * @throws Exception
     * @throws InvalidQueryException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();

        return 0;
    }
}
