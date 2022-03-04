<?php

/**
 * Cleanup the event models.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Domain\Model\Event;
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
    public const MODUS_HIDDEN = 'hide';
    public const MODUS_DELETED = 'delete';
    public const DEFAULT_WAIT_PERIOD = 14;
    public const DEFAULT_CLEANUP_REPOSITORY = \HDNET\Calendarize\Domain\Repository\EventRepository::class;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var RawIndexRepository
     */
    protected $rawIndexRepository;

    /**
     * @var DataMapper
     */
    protected $dataMapper;

    /**
     * @var IndexerService
     */
    protected $indexerService;

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param DataMapper $dataMapper
     */
    public function injectDataMapper(DataMapper $dataMapper): void
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * @param RawIndexRepository $rawIndexRepository
     */
    public function injectRawIndexRepository(RawIndexRepository $rawIndexRepository): void
    {
        $this->rawIndexRepository = $rawIndexRepository;
    }

    /**
     * @param IndexerService $indexerService
     */
    public function injectIndexerService(IndexerService $indexerService): void
    {
        $this->indexerService = $indexerService;
    }

    protected function configure()
    {
        $this->setDescription('Remove outdated events to keep a small footprint')
            ->addOption(
                'repositoryName',
                'r',
                InputOption::VALUE_REQUIRED,
                'The repository of the event to clean up',
                self::DEFAULT_CLEANUP_REPOSITORY
            )
            ->addOption(
                'modus',
                'm',
                InputOption::VALUE_REQUIRED,
                'What to do with cleaned Events? Set them \'hide\' or \'delete\'',
                self::MODUS_HIDDEN
            )
            ->addOption(
                'waitingPeriod',
                'w',
                InputOption::VALUE_REQUIRED,
                'How many days to wait after ending the Event before \'hide/delete\' it',
                self::DEFAULT_WAIT_PERIOD
            )->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'If this option is set, it only outputs the amount of records which would have been updated'
            );
    }

    /**
     * Cleanup the event models.
     * Remove outdated events to keep a small footprint. This gain maybe a little more performance.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repositoryName = $input->getOption('repositoryName');
        $modus = $input->getOption('modus');
        $waitingPeriod = (int)$input->getOption('waitingPeriod');

        /** @var Repository $repository */
        $repository = GeneralUtility::makeInstance($repositoryName);

        $io->section('Cleanup outdated events');
        // Index all events to start on a clean slate
        $this->indexerService->reindexAll();

        // repository name -> model name -> table name
        $objectType = ClassNamingUtility::translateRepositoryNameToModelName($repositoryName);
        $tableName = $this->dataMapper->getDataMap($objectType)->getTableName();

        $io->text('Tablename ' . $tableName);

        if (self::MODUS_HIDDEN === $modus
            && !isset($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'])
        ) {
            $io->error('Cannot hide events due to missing hidden/disabled field.');

            return 3;
        }

        $io->section('Find outdated events');
        // events uid, to be precise
        $events = $this->rawIndexRepository->findOutdatedEvents($tableName, $waitingPeriod);

        $io->text('Found ' . \count($events) . ' Events ready to process.');

        if (0 === \count($events) || true === $input->getOption('dry-run')) {
            return 0;
        }

        // climb through the events and hide/delete them
        foreach ($events as $event) {
            $uid = (int)$event['foreign_uid'];

            /** @var AbstractEntity $model */
            $model = $repository->findByUid($uid);

            $this->processEvent($repository, $model, $modus);
        }
        $io->text('Events processed.');

        $this->persistenceManager->persistAll();

        $io->section('Reindex all events');
        // after all this deleting ... reindex!
        $this->indexerService->reindexAll();

        return 0;
    }

    /**
     * Process the found Event and delete or hide it.
     *
     * @param EventRepository $repository
     * @param Event           $model
     * @param string          $modus
     */
    protected function processEvent(Repository $repository, AbstractEntity $model, string $modus)
    {
        // define the function for the delete-modus.
        $delete = static function ($repository, $model) {
            $repository->remove($model);
        };

        // define the function for the hide-modus.
        $hide = static function ($repository, $model) {
            $model->setHidden(true);
            $repository->update($model);
        };

        if (self::MODUS_DELETED === $modus) {
            $function = $delete;
        } else {
            $function = $hide;
        }

        $event = new CleanupEvent($modus, $repository, $model, $function);
        $this->eventDispatcher->dispatch($event);

        $myFunction = $event->getFunction();
        $myFunction($repository, $model);
    }
}
