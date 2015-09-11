<?php
/**
 * Import
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\Event;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Import
 *
 * @author Tim Lochmüller
 */
class ImportCommandController extends AbstractCommandController
{

    /**
     * Event repository
     *
     * @var \HDNET\Calendarize\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository;

    /**
     * Import command
     *
     * @param string $icsCalendarUri
     * @param int    $pid
     */
    public function importCommand($icsCalendarUri = null, $pid = null)
    {
        if ($icsCalendarUri === null || !filter_var($icsCalendarUri, FILTER_VALIDATE_URL)) {
            $this->enqueueMessage('You have to enter a valid URL to the iCalendar ICS', 'Error', FlashMessage::ERROR);
        }
        if (!MathUtility::canBeInterpretedAsInteger($pid)) {
            $this->enqueueMessage('You have to enter a valid PID for the new created elements', 'Error', FlashMessage::ERROR);
        }

        // fetch external URI and write to file
        $this->enqueueMessage('Start to checkout the calendar: ' . $icsCalendarUri, 'Calendar', FlashMessage::INFO);
        $relativeIcalFile = 'typo3temp/ical.' . GeneralUtility::shortMD5($icsCalendarUri) . '.ical';
        $absoluteIcalFile = GeneralUtility::getFileAbsFileName($relativeIcalFile);
        $content = GeneralUtility::getUrl($icsCalendarUri);
        GeneralUtility::writeFile($absoluteIcalFile, $content);

        // get Events from file
        $icalEvents = $this->getIcalEvents($absoluteIcalFile);
        $this->enqueueMessage('Found ' . sizeof($icalEvents) . ' events in the given calendar', 'Items', FlashMessage::INFO);
        $events = $this->prepareEvents($icalEvents);

        $this->enqueueMessage('This is just a first draft. There are same missing fields. Will be part of the next release',
            'Items', FlashMessage::ERROR);
        return;

        foreach ($events as $event) {
            $eventObject = $this->eventRepository->findOneByImportId($event['uid']);
            if ($eventObject instanceof Event) {
                // update
                $eventObject->setTitle($event['title']);
                $eventObject->setDescription($this->nl2br($event['description']));
                $this->eventRepository->update($eventObject);
                $this->enqueueMessage('Update Event Meta data: ' . $eventObject->getTitle(), 'Update');
            } else {
                // create
                $eventObject = new Event();
                $eventObject->setPid($pid);
                $eventObject->setImportId($event['uid']);
                $eventObject->setTitle($event['title']);
                $eventObject->setDescription($this->nl2br($event['description']));

                $configuration = new Configuration();
                $configuration->setType(Configuration::TYPE_TIME);
                $configuration->setFrequency(Configuration::FREQUENCY_NONE);
                /** @var \DateTime $startDate */
                $startDate = clone $event['start'];
                $startDate->setTime(0, 0, 0);
                $configuration->setStartDate($startDate);
                /** @var \DateTime $endDate */
                $endDate = clone $event['end'];
                $endDate->setTime(0, 0, 0);
                $configuration->setEndDate($endDate);

                $startTime = $this->dateTimeToDaySeconds($event['start']);
                if ($startTime > 0) {
                    $configuration->setStartTime($startTime);
                    $configuration->setEndTime($this->dateTimeToDaySeconds($event['end']));
                    $configuration->setAllDay(false);
                } else {
                    $configuration->setAllDay(true);
                }

                $eventObject->addCalendarize($configuration);

                $this->eventRepository->add($eventObject);
                $this->enqueueMessage('Add Event: ' . $eventObject->getTitle(), 'Add');
            }
        }
    }

    /**
     * Replace new lines
     *
     * @param string $string
     *
     * @return string
     */
    protected function nl2br($string)
    {
        $string = nl2br((string)$string);
        return str_replace('\\n', '<br />', $string);
    }

    /**
     * DateTime to day seconds
     *
     * @param \DateTime $dateTime
     *
     * @return int
     */
    protected function dateTimeToDaySeconds(\DateTime $dateTime)
    {
        $hours = (int)$dateTime->format('G');
        $minutes = ($hours * 60) + (int)$dateTime->format('i');
        return ($minutes * 60) + (int)$dateTime->format('s');
    }

    /**
     * Prepare the events
     *
     * @param array $icalEvents
     *
     * @return array
     */
    protected function prepareEvents($icalEvents)
    {
        $events = [];
        foreach ($icalEvents as $icalEvent) {
            $startDateTime = null;
            $endDateTime = null;
            try {
                $startDateTime = new \DateTime($icalEvent['DTSTART']);
            } catch (\Exception $ex) {
            }
            try {
                $endDateTime = new \DateTime($icalEvent['DTEND']);
            } catch (\Exception $ex) {
            }

            if ($startDateTime === null || $endDateTime === null) {
                $this->enqueueMessage('Could not convert the date in the right format of "' . $icalEvent['SUMMARY'] . '"',
                    'Warning', FlashMessage::WARNING);
            }

            $events[] = [
                'uid'         => $icalEvent['UID'],
                'start'       => $startDateTime,
                'end'         => $endDateTime,
                'title'       => $icalEvent['SUMMARY'],
                'description' => $icalEvent['DESCRIPTION'],
            ];
        }
        return $events;
    }

    /**
     * Get the events from the given ical file
     *
     * @param string $absoluteIcalFile
     *
     * @return array
     */
    protected function getIcalEvents($absoluteIcalFile)
    {
        require_once(ExtensionManagementUtility::extPath('calendarize', 'Resources/Private/Php/ics-parser/class.iCalReader.php'));
        return (new \ICal($absoluteIcalFile))->events();
    }
}