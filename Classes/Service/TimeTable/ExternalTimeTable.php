<?php

/**
 * External service.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Service\Ical\ICalServiceInterface;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * External service.
 */
class ExternalTimeTable extends AbstractTimeTable
{
    /**
     * Ical service.
     *
     * @var ICalServiceInterface
     */
    protected $iCalService;

    /**
     * Inject ical service.
     *
     * @param ICalServiceInterface $iCalService
     */
    public function injectICalServiceInterface(ICalServiceInterface $iCalService)
    {
        $this->iCalService = $iCalService;
    }

    /**
     * Modify the given times via the configuration.
     *
     * @param array         $times
     * @param Configuration $configuration
     *
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function handleConfiguration(array &$times, Configuration $configuration)
    {
        $url = $configuration->getExternalIcsUrl();
        if (!GeneralUtility::isValidUrl($url)) {
            HelperUtility::createFlashMessage(
                'Configuration with invalid ICS URL: ' . $url,
                'Index ICS URL',
                FlashMessage::ERROR
            );

            return;
        }

        $fileName = $this->getCachedUrlFile($url);
        $externalTimes = $this->iCalService->getEvents($fileName);

        foreach ($externalTimes as $event) {
            $time = [
                'start_date' => $event->getStartDate(),
                'end_date' => $event->getEndDate(),
                'start_time' => $event->getStartTime(),
                'end_time' => $event->getEndTime(),
                'all_day' => $event->isAllDay(),
                'open_end_time' => $event->isOpenEndTime(),
                'state' => $event->getState(),
            ];
            $time['pid'] = $configuration->getPid();
            $time['state'] = $configuration->getState();
            $times[$this->calculateEntryKey($time)] = $time;
        }
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
