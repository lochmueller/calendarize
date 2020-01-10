<?php

/**
 * ICS Service.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Service\TimeTable\ExternalTimeTable;
use HDNET\Calendarize\Utility\DateTimeUtility;
use JMBTechnologyLimited\ICalDissect\ICalEvent;
use JMBTechnologyLimited\ICalDissect\ICalParser;
use Sabre\VObject\Reader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ICS Service.
 */
class IcsReaderService extends AbstractService
{
    /**
     * Generate the times of the given URL.
     *
     * @param string $url
     *
     * @return array
     */
    public function getTimes(string $url): array
    {
        $fileName = $this->getCachedUrlFile($url);
        $times = $this->buildWithICalDissect($fileName);
        // $test = $this->buildWithVObject($fileName);
        return $times;
    }

    /**
     * Build with iCal dissect.
     *
     * @param string $filename
     *
     * @return array
     */
    protected function buildWithICalDissect(string $filename): array
    {
        $backend = new ICalParser();
        if (!$backend->parseFromFile($filename)) {
            return [];
        }
        $events = $backend->getEvents();
        $times = [];
        foreach ($events as $event) {
            /** @var $event ICalEvent */
            $startTime = DateTimeUtility::getDaySecondsOfDateTime($event->getStart());
            $endTime = DateTimeUtility::getDaySecondsOfDateTime($event->getEnd());
            if (86399 === $endTime) {
                $endTime = 0;
            }

            $times[] = [
                'start_date' => $event->getStart(),
                'end_date' => $this->getEventsFixedEndDate($event),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'all_day' => 0 === $endTime,
            ];
        }

        return $times;
    }

    /**
     * Build with Vobject.
     *
     * @param string $filename
     *
     * @return array
     *
     * @todo implement
     */
    protected function buildWithVObject(string $filename): array
    {
        $vcalendar = Reader::read(
            GeneralUtility::getUrl($filename)
        );
        $times = [];
        foreach ($vcalendar->VEVENT as $event) {
            /*DTSTAMP => array(1 item)
      UID => array(1 item)
      DTSTART => array(1 item)
      DTEND => array(1 item)*/
            //DebuggerUtility::var_dump($event->TESTST);
            //DebuggerUtility::var_dump($event->DTSTAMP);
            //DebuggerUtility::var_dump($event->DTSTART);
            //DebuggerUtility::var_dump($event->DTEND);

            $times[] = [
                'start_date' => $event->DTSTART->getDateTime(),
                'end_date' => 0, // $this->getEventsFixedEndDate($event),
                'start_time' => 0,
                'end_time' => 0,
                'all_day' => true,
            ];
        }

        return $times;
    }

    /**
     * Fixes a parser related bug where the DTEND is EXCLUSIVE.
     * The parser uses it inclusive so every event is one day
     * longer than it should be.
     *
     * @param ICalEvent $event
     *
     * @return \DateTime
     */
    protected function getEventsFixedEndDate(ICalEvent $event)
    {
        if (!$event->getEnd() instanceof \DateTimeInterface) {
            return $event->getStart();
        }

        $end = clone $event->getEnd();
        $end->sub(new \DateInterval('P1D'));
        if ($end->format('Ymd') === $event->getStart()->format('Ymd')) {
            return $end;
        }

        return $event->getEnd();
    }

    /**
     * Get cached URL file.
     *
     * @param string $url
     *
     * @return string
     */
    protected function getCachedUrlFile(string $url): string
    {
        $tempFileName = $this->getCheckedCacheFolder() . \md5($url);
        if (!\is_file($tempFileName) || \filemtime($tempFileName) < (\time() - DateTimeUtility::SECONDS_HOUR)) {
            $icsFile = GeneralUtility::getUrl($url);
            GeneralUtility::writeFile($tempFileName, $icsFile);
        }

        return $tempFileName;
    }

    /**
     * Return the cache folder and check if the folder exists.
     *
     * @return string
     */
    protected function getCheckedCacheFolder(): string
    {
        $cacheFolder = GeneralUtility::getFileAbsFileName('typo3temp/var/transient/calendarize/');
        if (!\is_dir($cacheFolder)) {
            GeneralUtility::mkdir_deep($cacheFolder);
        }

        return $cacheFolder;
    }
}
