<?php

/**
 * External service.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Exception\UnableToGetFileForUrlException;
use HDNET\Calendarize\Service\Ical\ICalServiceInterface;
use HDNET\Calendarize\Service\Ical\ICalUrlService;
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
     * @var ICalUrlService
     */
    protected ICalUrlService $iCalUrlService;

    /**
     * Inject ical service.
     *
     * @param ICalServiceInterface $iCalService
     */
    public function injectICalServiceInterface(ICalServiceInterface $iCalService)
    {
        $this->iCalService = $iCalService;
    }

    public function injectICalUrlService(ICalUrlService $icalUrlService)
    {
        $this->iCalUrlService = $icalUrlService;
    }

    /**
     * Modify the given times via the configuration.
     *
     * @param array         $times
     * @param Configuration $configuration
     */
    public function handleConfiguration(array &$times, Configuration $configuration)
    {
        $externalIcsUrl = $configuration->getExternalIcsUrl();
        try {
            $fileName = $this->iCalUrlService->getOrCreateLocalFileForUrl($externalIcsUrl);
        } catch (UnableToGetFileForUrlException $e) {
            HelperUtility::createTranslatedTitleFlashMessage(
                $e->getMessage(),
                'flashMessage.invalidExternalUrl.title',
                FlashMessage::ERROR
            );

            return;
        }

        try {
            $externalTimes = $this->iCalService->getEvents($fileName);
        } catch (\Exception $e) {
            HelperUtility::createTranslatedTitleFlashMessage(
                $e->getMessage(),
                'flashMessage.unableToProcessEvents.title',
                FlashMessage::ERROR
            );

            return;
        } finally {
            GeneralUtility::unlink_tempfile($fileName);
        }

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
}
