<?php
/**
 * Import
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Command;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Import
 *
 * @author Tim Lochmüller
 */
class ImportCommandController extends AbstractCommandController
{

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
            return;
        }
        if (!MathUtility::canBeInterpretedAsInteger($pid)) {
            $this->enqueueMessage('You have to enter a valid PID for the new created elements', 'Error', FlashMessage::ERROR);
            return;
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

        $this->enqueueMessage('Found ' . sizeof($events) . ' events in ' . $icsCalendarUri, 'Items', FlashMessage::INFO);

        /** @var Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

        $this->enqueueMessage('Send the ' . __CLASS__ . '::importCommand signal for each event.', 'Signal', FlashMessage::INFO);
        foreach ($events as $event) {
            $arguments = [
                'event'             => $event,
                'commandController' => $this,
                'pid'               => $pid,
                'handled'           => false,
            ];
            $signalSlotDispatcher->dispatch(__CLASS__, 'importCommand', $arguments);
        }
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