<?php

/**
 * Import.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Service\IndexerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Import.
 */
class ImportCommandController extends Command
{
    protected function configure()
    {
        $this->setDescription('Imports a iCalendar ICS into a page ID')
            ->addArgument('icsCalendarUri', InputArgument::REQUIRED, 'The URI of the iCalendar ICS')
            ->addArgument('pid', InputArgument::REQUIRED, 'The page ID to create new elements');
    }

    /**
     * Executes the command for importing a iCalendar ICS into a page ID.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $icsCalendarUri = $input->getArgument('icsCalendarUri');
        $pid = $input->getArgument('pid');

        if (!\filter_var($icsCalendarUri, FILTER_VALIDATE_URL)) {
            $io->error('You have to enter a valid URL to the iCalendar ICS');

            return 1;
        }
        if (!MathUtility::canBeInterpretedAsInteger($pid)) {
            $io->error('You have to enter a valid PID for the new created elements');

            return 2;
        }
        $pid = (int)$pid;

        // fetch external URI and write to file
        $io->section('Start to checkout the calendar: ' . $icsCalendarUri);
        $relativeIcalFile = 'typo3temp/ical.' . GeneralUtility::shortMD5($icsCalendarUri) . '.ical';
        $absoluteIcalFile = GeneralUtility::getFileAbsFileName($relativeIcalFile);
        $content = GeneralUtility::getUrl($icsCalendarUri);
        GeneralUtility::writeFile($absoluteIcalFile, $content);

        // get Events from file
        $icalEvents = $this->getIcalEvents($absoluteIcalFile);
        $events = $this->prepareEvents($icalEvents, $io);

        $io->text('Found ' . \count($events) . ' events in ' . $icsCalendarUri);

        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);

        $io->section('Send the ' . __CLASS__ . '::importCommand signal for each event.');
        $io->progressStart(\count($events));
        foreach ($events as $event) {
            $arguments = [
                'event' => $event,
                'io' => $io,
                'pid' => $pid,
                'handled' => false,
            ];
            $signalSlotDispatcher->dispatch(__CLASS__, 'importCommand', $arguments);
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->section('Run Reindex proces after import');
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();

        return 0;
    }

    /**
     * Prepare the events.
     *
     * @param array $icalEvents
     *
     * @param SymfonyStyle $io
     * @return array
     */
    protected function prepareEvents(array $icalEvents, SymfonyStyle $io)
    {
        $events = [];
        foreach ($icalEvents as $icalEvent) {
            $startDateTime = null;
            $endDateTime = null;
            try {
                $startDateTime = new \DateTime($icalEvent['DTSTART']);
                if ($icalEvent['DTEND']) {
                    $endDateTime = new \DateTime($icalEvent['DTEND']);
                } else {
                    $endDateTime = clone $startDateTime;
                    $endDateTime->add(new \DateInterval($icalEvent['DURATION']));
                }
            } catch (\Exception $ex) {
                $io->warning('Could not convert the date in the right format of "' . $icalEvent['SUMMARY'] . '"');
                continue;
            }

            $events[] = [
                'uid' => $icalEvent['UID'],
                'start' => $startDateTime,
                'end' => $endDateTime,
                'title' => $icalEvent['SUMMARY'] ? $icalEvent['SUMMARY'] : '',
                'description' => $icalEvent['DESCRIPTION'] ? $icalEvent['DESCRIPTION'] : '',
                'location' => $icalEvent['LOCATION'] ? $icalEvent['LOCATION'] : '',
            ];
        }

        return $events;
    }

    /**
     * Get the events from the given ical file.
     *
     * @param string $absoluteIcalFile
     *
     * @return array
     */
    protected function getIcalEvents($absoluteIcalFile)
    {
        if (!\class_exists('ICal')) {
            require_once ExtensionManagementUtility::extPath(
                'calendarize',
                'Resources/Private/Php/ics-parser/class.iCalReader.php'
            );
        }

        return (array)(new \ICal($absoluteIcalFile))->events();
    }
}
