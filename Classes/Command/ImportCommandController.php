<?php

/**
 * Import.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Event\ImportSingleIcalEvent;
use HDNET\Calendarize\Exception\UnableToGetFileForUrlException;
use HDNET\Calendarize\Service\Ical\ICalServiceInterface;
use HDNET\Calendarize\Service\Ical\ICalUrlService;
use HDNET\Calendarize\Service\IndexerService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Import.
 */
class ImportCommandController extends Command
{
    /**
     * @var ICalServiceInterface
     */
    protected $iCalService;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var IndexerService
     */
    protected $indexerService;

    /**
     * @var ICalUrlService
     */
    protected $iCalUrlService;

    /**
     * ImportCommandController constructor.
     *
     * @param ICalServiceInterface     $iCalService
     * @param EventDispatcherInterface $eventDispatcher
     * @param IndexerService           $indexerService
     */
    public function __construct(
        ICalServiceInterface $iCalService,
        EventDispatcherInterface $eventDispatcher,
        IndexerService $indexerService,
        ICalUrlService $iCalUrlService
    ) {
        $this->iCalService = $iCalService;
        $this->eventDispatcher = $eventDispatcher;
        $this->indexerService = $indexerService;
        $this->iCalUrlService = $iCalUrlService;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Imports a iCalendar ICS into a page ID')
            ->addArgument(
                'icsCalendarUri',
                InputArgument::REQUIRED,
                'The URL of the iCalendar ICS or local file (t3://file?uid=23)'
            )
            ->addArgument(
                'pid',
                InputArgument::REQUIRED,
                'The page ID to create new elements'
            )
            ->addOption(
                'since',
                's',
                InputOption::VALUE_OPTIONAL,
                "Imports all events since the given date.\n"
                . 'Valid PHP date format e.g. "2014-04-14", "-10 days"' . "\n"
                . '(Note: use --since="-x days" syntax on the console)'
            );
    }

    /**
     * Executes the command for importing a iCalendar ICS into a page ID.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $icsCalendarUri = (string)$input->getArgument('icsCalendarUri');
        if (!GeneralUtility::isValidUrl($icsCalendarUri)) {
            $io->error('You have to enter a valid URL to the iCalendar ICS');

            return 1;
        }

        $pid = $input->getArgument('pid');
        if (!MathUtility::canBeInterpretedAsInteger($pid)) {
            $io->error('You have to enter a valid PID for the new created elements');

            return 1;
        }
        $pid = (int)$pid;

        // Process skip
        $since = $input->getOption('since');
        $ignoreBeforeDate = null;
        if (null !== $since) {
            $ignoreBeforeDate = new \DateTime($since);
            $io->text('Skipping all events before ' . $ignoreBeforeDate->format(\DateTimeInterface::ATOM));
        }

        // Fetch external URI and write it to a temporary file
        $io->section('Start to checkout the calendar');

        try {
            $icalFile = $this->iCalUrlService->getOrCreateLocalFileForUrl($icsCalendarUri);
        } catch (UnableToGetFileForUrlException $e) {
            $io->error('Invalid URL: ' . $e->getMessage());

            return 1;
        }
        try {
            // Parse calendar
            $events = $this->iCalService->getEvents($icalFile);
        } catch (\Exception $e) {
            $io->error('Unable to process events');
            $io->writeln($e->getMessage());
            if ($io->isVerbose()) {
                $io->writeln($e->getTraceAsString());
            }

            return 1;
        } finally {
            // Remove temporary file
            GeneralUtility::unlink_tempfile($icalFile);
        }

        $io->text('Found ' . \count($events) . ' events in ' . $icsCalendarUri);

        $io->section('Send ImportSingleIcalEvent for each event');
        $io->progressStart(\count($events));

        $skipCount = $dispatchCount = 0;
        foreach ($events as $event) {
            // Skip events before given date
            if (($event->getEndDate() ?? $event->getStartDate()) < $ignoreBeforeDate) {
                $io->progressAdvance();
                ++$skipCount;
                continue;
            }

            $this->eventDispatcher->dispatch(new ImportSingleIcalEvent($event, $pid));
            ++$dispatchCount;
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->text('Dispatched ' . $dispatchCount . ' Events');
        $io->text('Skipped ' . $skipCount . ' Events');

        $io->section('Run Reindex process after import');
        $this->indexerService->reindexAll();

        return 0;
    }
}
