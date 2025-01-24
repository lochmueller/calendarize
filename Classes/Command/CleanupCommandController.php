<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Domain\Repository\RawIndexRepository;
use HDNET\Calendarize\Event\CleanupEvent;
use HDNET\Calendarize\Service\IndexerService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Cleanup the event models.
 */
class CleanupCommandController extends Command
{
    public const MODE_HIDDEN = 'hide';
    public const MODE_DELETED = 'delete';
    public const DEFAULT_WAIT_PERIOD = 14;
    public const DEFAULT_CLEANUP_REPOSITORY = EventRepository::class;

    public function __construct(
        protected PersistenceManager $persistenceManager,
        protected EventDispatcherInterface $eventDispatcher,
        protected RawIndexRepository $rawIndexRepository,
        protected IndexerService $indexerService,
        protected DataMapper $dataMapper,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption(
                'repositoryName',
                'r',
                InputOption::VALUE_REQUIRED,
                'The repository of the event to clean up',
                self::DEFAULT_CLEANUP_REPOSITORY,
            )
            ->addOption(
                'modus',
                'm',
                InputOption::VALUE_REQUIRED,
                'What to do with cleaned Events? Set them \'hide\' or \'delete\'',
                self::MODE_HIDDEN,
            )
            ->addOption(
                'waitingPeriod',
                'w',
                InputOption::VALUE_REQUIRED,
                'How many days to wait after ending the Event before \'hide/delete\' it',
                self::DEFAULT_WAIT_PERIOD,
            )->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'If this option is set, it only outputs the amount of records which would have been updated',
            );
    }

    /**
     * Cleanup the event models.
     * Remove outdated events to keep a small footprint. This gain maybe a little more performance.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Cleanup outdated events');

        $repositoryName = $input->getOption('repositoryName');
        $mode = $input->getOption('modus');
        $waitingPeriod = (int)$input->getOption('waitingPeriod');

        /** @var Repository $repository */
        $repository = GeneralUtility::makeInstance($repositoryName);

        $io->section('Reindex events');
        // Index all events to start on a clean slate
        $this->indexerService->reindexAll();

        // repository name -> model name -> table name
        $io->section('Find outdated events');
        $objectType = ClassNamingUtility::translateRepositoryNameToModelName($repositoryName);
        $tableName = $this->dataMapper->getDataMap($objectType)->getTableName();

        $io->text('Tablename ' . $tableName);

        if (
            self::MODE_HIDDEN === $mode
            && !isset($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'])
        ) {
            $io->error('Cannot hide events due to missing hidden/disabled field.');

            return self::FAILURE;
        }

        // events uid, to be precise
        $events = $this->rawIndexRepository->findOutdatedEvents($tableName, $waitingPeriod);

        $io->text('Found ' . \count($events) . ' Events ready to process.');

        if (0 === \count($events) || true === $input->getOption('dry-run')) {
            return self::SUCCESS;
        }

        $io->section('Cleanup outdated events now');
        // climb through the events and hide/delete them
        foreach ($events as $event) {
            /** @var AbstractEntity $model */
            $model = $repository->findByUid((int)$event['foreign_uid']);
            $this->processEvent($repository, $model, $mode);
        }
        $io->text('Events processed.');

        $this->persistenceManager->persistAll();

        $io->section('Reindex all events (again)');
        // after all this deleting ... reindex!
        $this->indexerService->reindexAll();

        return self::SUCCESS;
    }

    /**
     * Process the found Event and delete or hide it.
     */
    protected function processEvent(Repository $repository, AbstractEntity $model, string $mode): void
    {
        // define the function for the delete-mode.
        $delete = static function ($repository, $model) {
            $repository->remove($model);
        };

        // define the function for the hide-mode.
        $hide = static function ($repository, $model) {
            $model->setHidden(true);
            $repository->update($model);
        };

        if (self::MODE_DELETED === $mode) {
            $function = $delete;
        } else {
            $function = $hide;
        }

        $event = new CleanupEvent($mode, $repository, $model, $function);
        $this->eventDispatcher->dispatch($event);

        $myFunction = $event->getFunction();
        $myFunction($repository, $model);
    }
}
