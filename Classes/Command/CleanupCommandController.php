<?php

/**
 * Cleanup the event models.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Event\CleanupEvent;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Cleanup the event models.
 */
class CleanupCommandController extends Command
{
    const MODUS_HIDDEN = 'hide';
    const MODUS_DELETED = 'delete';
    const DEFAULT_WAIT_PERIOD = 14;
    const DEFAULT_CLEANUP_REPOSITORY = \HDNET\Calendarize\Domain\Repository\EventRepository::class;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $repositoryName = $input->getOption('repositoryName');
        $modus = $input->getOption('modus');
        $waitingPeriod = $input->getOption('waitingPeriod');

        /** @var EventRepository $repository */
        $repository = GeneralUtility::makeInstance($repositoryName);

        if (!($repository instanceof EventRepository)) {
            return 1;
        }

        $io->section('Reindex all events');
        // Index all events to start on a clean slate
        $this->reIndex();

        // get tablename from repository, works only with the extended EventRepository
        $tableName = $repository->getTableName();

        if (!$tableName) {
            $io->error('No tablename found on your given Repository! [' . $repositoryName . ']');

            return 2;
        }

        $io->text('Tablename ' . $tableName);

        $io->section('Find outdated events');
        // events uid, to be precise
        $events = $this->findOutdatedEvents($tableName, $waitingPeriod);

        $io->text('Just found ' . \count($events) . ' Events ready to process.');

        // climb thru the events and hide/delete them
        foreach ($events as $event) {
            $uid = (int)$event['foreign_uid'];

            $model = $repository->findByUid($uid);

            if (!($model instanceof Event)) {
                $io->error('Object with uid [' . $uid . '] is not an instance of the event base model.');
                continue;
            }

            $this->processEvent($repository, $model, $modus);
        }

        $this->persistenceManager->persistAll();

        $io->section('Reindex all events');
        // after all this deleting ... reindex!
        $this->reIndex();

        return 0;
    }

    /**
     * Process the found Event and delete or hide it.
     *
     * @param EventRepository $repository
     * @param Event           $model
     * @param string          $modus
     */
    protected function processEvent(EventRepository $repository, Event $model, $modus)
    {
        // define the function for the delete-modus.
        $delete = function ($repository, $model) {
            $repository->remove($model);
        };

        // define the function for the hide-modus.
        $hide = function ($repository, $model) {
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

    /**
     * Find outdated events.
     *
     * @param string $tableName
     * @param int    $waitingPeriod
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function findOutdatedEvents($tableName, $waitingPeriod): array
    {
        // calculate the waiting time
        $interval = 'P' . (int)$waitingPeriod . 'D';
        $now = DateTimeUtility::getNow();
        $now->sub(new \DateInterval($interval));

        // search for outdated events
        $table = IndexerService::TABLE_NAME;

        $q = HelperUtility::getDatabaseConnection($table)->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(HiddenRestriction::class));

        $foreignUids = $q->select('foreign_uid')
            ->from($table)
            ->where($q->expr()
                ->gt('end_date', $q->createNamedParameter($now->format('Y-m-d'))))
            ->andWhere($q->expr()
                ->eq('foreign_table', $q->createNamedParameter($tableName)))
            ->execute()
            ->fetchAll();

        $foreignUids = array_map(function ($item) {
            return (int)$item['foreign_uid'];
        }, $foreignUids);

        $q->select('foreign_uid')
            ->from($table)
            ->where($q->expr()
                ->andX($q->expr()
                    ->lt('end_date', $q->createNamedParameter($now->format('Y-m-d'))), $q->expr()
                    ->eq('foreign_table', $q->createNamedParameter($tableName)), $q->expr()
                    ->notIn('foreign_uid', $foreignUids)));

        $rows = $q->execute()->fetchAll();

        return $rows;
    }

    /**
     * Reindex the Events.
     * This may take some time.
     */
    protected function reIndex()
    {
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();
    }
}
