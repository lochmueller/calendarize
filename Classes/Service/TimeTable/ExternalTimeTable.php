<?php
/**
 * External service
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use JMBTechnologyLimited\ICalDissect\ICalEvent;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * External service
 *
 * @author Tim Lochmüller
 */
class ExternalTimeTable extends AbstractTimeTable
{

    /**
     * ICS reader service
     *
     * @var \HDNET\Calendarize\Service\IcsReaderService
     * @inject
     */
    protected $icsReaderService;

    /**
     * Modify the given times via the configuration
     *
     * @param array         $times
     * @param Configuration $configuration
     *
     * @return void
     */
    public function handleConfiguration(array &$times, Configuration $configuration)
    {
        $url = $configuration->getExternalIcsUrl();
        if (!GeneralUtility::isValidUrl($url)) {
            HelperUtility::createFlashMessage('Configuration with invalid ICS URL: ' . $url, 'Index ICS URL',
                FlashMessage::ERROR);
            return;
        }

        $events = $this->icsReaderService->toArray($url);
        foreach ($events as $event) {
            /** @var $event ICalEvent */
            $startTime = DateTimeUtility::getDaySecondsOfDateTime($event->getStart());
            $endTime = DateTimeUtility::getDaySecondsOfDateTime($event->getEnd());
            if ($endTime === self::DAY_END) {
                $endTime = 0;
            }

            $entry = [
                'pid'        => 0,
                'start_date' => $event->getStart(),
                'end_date'   => $this->getEventsFixedEndDate($event),
                'start_time' => $startTime,
                'end_time'   => $endTime,
                'all_day'    => $endTime === 0,
            ];
            $times[] = $entry;
        }
    }

    /**
     * Fixes a parser related bug where the DTEND is EXCLUSIVE.
     * The parser uses it inclusive so every event is one day
     * longer than it should be.
     *
     * @param ICalEvent $event
     * @return \DateTime
     */
    protected function getEventsFixedEndDate(ICalEvent $event)
    {
        if (!$event->getEnd() instanceof \DateTime) {
            return $event->getStart();
        }

        $end = clone($event->getEnd());
        $end->sub(new \DateInterval('P1D'));
        if ($end->format('Ymd') === $event->getStart()->format('Ymd')) {
            return $end;
        }

        return $event->getEnd();
    }

}
