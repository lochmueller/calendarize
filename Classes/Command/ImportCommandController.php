<?php

/**
 * Import.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Event\ImportSingleIcalEvent;
use HDNET\Calendarize\Service\Ical\ICalServiceInterface;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Utility\DateTimeUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Import.
 */
class ImportCommandController extends Command
{

    /**
     * @var ICalServiceInterface $iCalService
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
     * ImportCommandController constructor.
     * @param string|null $name
     * @param ICalServiceInterface $iCalService
     * @param EventDispatcherInterface $eventDispatcher
     * @param IndexerService $indexerService
     */
    public function __construct(
        string $name = null,
        ICalServiceInterface $iCalService,
        EventDispatcherInterface $eventDispatcher,
        IndexerService $indexerService
    ) {
        $this->iCalService = $iCalService;
        $this->eventDispatcher = $eventDispatcher;
        $this->indexerService = $indexerService;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Imports a iCalendar ICS into a page ID')
            ->addArgument(
                'icsCalendarUri',
                InputArgument::REQUIRED,
                'The URI of the iCalendar ICS'
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
                . 'Valid PHP date format e.g. "2014-04-14", "-10 days" (Note: use --since="-x days" syntax)',
                DateTimeUtility::getNow()->format('Y-m-d')
            );
    }

    /**
     * Executes the command for importing a iCalendar ICS into a page ID.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an exit code
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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
        if ($since !== null) {
            $ignoreBeforeDate = new \DateTime($since);
            $io->text('Skipping all events before ' . $ignoreBeforeDate->format(\DateTimeInterface::ATOM));
        }

        // Fetch external URI and write it to a temporary file
        $io->section('Start to checkout the calendar');

        $content = GeneralUtility::getUrl($icsCalendarUri);
        if ($content === false) {
            $io->error('Unable to get the content of ' . $icsCalendarUri . '.');
            return 1;
        }

        $icalFile = Environment::getVarPath() . '/transient/.' . 'ical-' . GeneralUtility::shortMD5($icsCalendarUri) . '.ics';
        $tempResult = GeneralUtility::writeFileToTypo3tempDir($icalFile, $content);
        if ($tempResult !== null) {
            $io->error('Unable to write to "' . $icalFile . '". Reason: ' . $tempResult);
            return 1;
        }

        // Parse calendar
        $events = $this->iCalService->getEvents($icalFile);

        // Remove temporary file
        unlink($icalFile);

        $io->text('Found ' . \count($events) . ' events in ' . $icsCalendarUri);

        $io->section('Send ImportSingleIcalEvent for each event');
        $io->progressStart(\count($events));

        $skipCount = $dispatchCount = 0;
        foreach ($events as $event) {
            // Skip events before given date
            if (($event->getEndDate() ?? $event->getStartDate()) < $ignoreBeforeDate) {
                $io->progressAdvance();
                $skipCount++;
                continue;
            }

            $this->eventDispatcher->dispatch(new ImportSingleIcalEvent($event, $pid));
            $dispatchCount++;
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
