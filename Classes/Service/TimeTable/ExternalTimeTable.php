<?php

/**
 * External service.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Service\IcsReaderService;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * External service.
 */
class ExternalTimeTable extends AbstractTimeTable
{
    /**
     * ICS reader service.
     *
     * @var \HDNET\Calendarize\Service\IcsReaderService
     */
    protected $icsReaderService;

    /**
     * Inject ICS reader service.
     *
     * @param IcsReaderService $icsReaderService
     */
    public function injectIcsReaderService(IcsReaderService $icsReaderService)
    {
        $this->icsReaderService = $icsReaderService;
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

        $externalTimes = $this->icsReaderService->getTimes($url);
        foreach ($externalTimes as $time) {
            $time['pid'] = $configuration->getPid();
            $time['state'] = $configuration->getState();
            $times[$this->calculateEntryKey($time)] = $time;
        }
    }
}
