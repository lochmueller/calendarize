<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Exception\UnableToGetFileForUrlException;
use HDNET\Calendarize\Service\Ical\ICalServiceInterface;
use HDNET\Calendarize\Service\Ical\ICalUrlService;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * External service.
 */
class ExternalTimeTable extends AbstractTimeTable
{
    /**
     * Ical service.
     */
    protected ICalServiceInterface $iCalService;

    /**
     * @var ICalUrlService
     */
    protected ICalUrlService $iCalUrlService;

    /**
     * Inject ical service.
     */
    public function injectICalServiceInterface(ICalServiceInterface $iCalService): void
    {
        $this->iCalService = $iCalService;
    }

    public function injectICalUrlService(ICalUrlService $icalUrlService): void
    {
        $this->iCalUrlService = $icalUrlService;
    }

    /**
     * Modify the given times via the configuration.
     */
    public function handleConfiguration(array &$times, Configuration $configuration): void
    {
        $externalIcsUrl = $configuration->getExternalIcsUrl();
        try {
            $fileName = $this->iCalUrlService->getOrCreateLocalFileForUrl($externalIcsUrl);
        } catch (UnableToGetFileForUrlException $e) {
            HelperUtility::createTranslatedTitleFlashMessage(
                $e->getMessage(),
                'flashMessage.invalidExternalUrl.title',
                ContextualFeedbackSeverity::ERROR,
            );

            return;
        }

        try {
            $externalTimes = $this->iCalService->getEvents($fileName);
        } catch (\Exception $e) {
            HelperUtility::createTranslatedTitleFlashMessage(
                $e->getMessage(),
                'flashMessage.unableToProcessEvents.title',
                ContextualFeedbackSeverity::ERROR,
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
                // @todo: shouldnt this be not overridden?
                'state' => $event->getState(),
            ];
            $time['pid'] = $configuration->getPid();
            $time['state'] = $configuration->getState();
            $times[$this->calculateEntryKey($time)] = $time;
        }
    }
}
