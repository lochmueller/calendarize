<?php
/**
 * Time exclude service
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Service\RecurrenceService;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Time exclude service
 *
 * @author Tim Lochmüller
 */
class TimeExcludeTimeTable extends TimeTimeTable
{

    /**
     * Modify the given times via the configuration
     *
     * @param array $times
     * @param Configuration $configuration
     *
     * @return void
     */
    public function handleConfiguration(array &$times, Configuration $configuration)
    {
        $startTime = $configuration->isAllDay() ? null : $configuration->getStartTime();
        $endTime = $configuration->isAllDay() ? null : $configuration->getEndTime();
        $baseEntry = [
            'pid' => $configuration->getPid(),
            'start_date' => $configuration->getStartDate(),
            'end_date' => $configuration->getEndDate() ?: $configuration->getStartDate(),
            'start_time' => $startTime,
            'end_time' => $endTime == 0 ? TimeTimeTable::DAY_END : $endTime,
            'all_day' => $configuration->isAllDay(),
        ];
        $this->validateBaseEntry($baseEntry);
        $excludeTimes = [$baseEntry];
        $this->addFrequencyItems($excludeTimes, $configuration, $baseEntry);
        $this->addRecurrenceItems($excludeTimes, $configuration, $baseEntry);

        $times = $this->checkAndRemoveTimes($times, $excludeTimes);
    }
}
