<?php
/**
 * Time service
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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Time service
 *
 * @author Tim Lochmüller
 */
class TimeTimeTable extends AbstractTimeTable
{

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
        $startTime = $configuration->isAllDay() ? null : $configuration->getStartTime();
        $endTime = $configuration->isAllDay() ? null : $configuration->getEndTime();
        $baseEntry = [
            'pid'        => $configuration->getPid(),
            'start_date' => $configuration->getStartDate(),
            'end_date'   => $configuration->getEndDate() ?: $configuration->getStartDate(),
            'start_time' => $startTime,
            'end_time'   => $endTime == 0 ? self::DAY_END : $endTime,
            'all_day'    => $configuration->isAllDay(),
        ];
        $this->validateBaseEntry($baseEntry);
        $times[] = $baseEntry;
        $this->addFrequencyItems($times, $configuration, $baseEntry);
        $this->addRecurrenceItems($times, $configuration, $baseEntry);
    }

    /**
     * Validate the base entry, if there are logica mistakes
     *
     * @param array $baseEntry
     */
    protected function validateBaseEntry(array $baseEntry)
    {
        $message = null;
        if ($baseEntry['start_date'] > $baseEntry['end_date']) {
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                LocalizationUtility::translate(
                    'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:wrong.date.message',
                    'calendarize'
                ),
                LocalizationUtility::translate(
                    'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:wrong.date',
                    'calendarize'
                ),
                FlashMessage::ERROR
            );
        } elseif (!$baseEntry['all_day'] && $baseEntry['start_date']->format('d.m.Y') == $baseEntry['end_date']->format('d.m.Y') && $baseEntry['start_time'] > $baseEntry['end_time']) {
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                LocalizationUtility::translate(
                    'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:wrong.time.message',
                    'calendarize'
                ),
                LocalizationUtility::translate(
                    'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:wrong.time',
                    'calendarize'
                ),
                FlashMessage::ERROR
            );
        }
        if ($message) {
            $flashMessageService = HelperUtility::create(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $messageQueue->addMessage($message);
        }
    }

    /**
     * Add frequency items
     *
     * @param array         $times
     * @param Configuration $configuration
     * @param array         $baseEntry
     */
    protected function addFrequencyItems(array &$times, Configuration $configuration, array $baseEntry)
    {
        $frequencyIncrement = $this->getFrequencyIncrement($configuration);
        if (!$frequencyIncrement) {
            return;
        }
        $amountCounter = $configuration->getCounterAmount();
        $tillDate = $configuration->getTillDate();
        $maxLimit = $this->getFrequencyLimitPerItem();
        $lastLoop = $baseEntry;
        for ($i = 0; $i < $maxLimit && ($amountCounter === 0 || $i < $amountCounter); $i++) {
            $loopEntry = $this->createNextLoopEntry($lastLoop, $frequencyIncrement);

            if ($tillDate instanceof \DateTimeInterface && $loopEntry['start_date'] > $tillDate) {
                break;
            }

            $lastLoop = $loopEntry;
            $times[] = $loopEntry;
        }
    }

    /**
     * Create the next loop entry
     *
     * @param array  $loopEntry
     * @param string $modification
     *
     * @return mixed
     */
    protected function createNextLoopEntry($loopEntry, $modification)
    {
        /** @var $startDate \DateTime */
        $startDate = clone $loopEntry['start_date'];
        $startDate->modify($modification);
        $loopEntry['start_date'] = $startDate;

        /** @var $endDate \DateTime */
        $endDate = clone $loopEntry['end_date'];
        $endDate->modify($modification);
        $loopEntry['end_date'] = $endDate;
        return $loopEntry;
    }

    /**
     * Get the frequency date increment
     *
     * @param Configuration $configuration
     *
     * @return string
     */
    protected function getFrequencyIncrement(Configuration $configuration)
    {
        $interval = $configuration->getCounterInterval() <= 1 ? 1 : $configuration->getCounterInterval();
        switch ($configuration->getFrequency()) {
            case Configuration::FREQUENCY_DAILY:
                $intervalValue = '+' . $interval . ' days';
                break;
            case Configuration::FREQUENCY_WEEKLY:
                $intervalValue = '+' . $interval . ' weeks';
                break;
            case Configuration::FREQUENCY_MONTHLY:
                if ($configuration->getRecurrence() !== Configuration::RECURRENCE_NONE) {
                    return false;
                }
                $intervalValue = '+' . $interval . ' months';
                break;
            case Configuration::FREQUENCY_YEARLY:
                if ($configuration->getRecurrence() !== Configuration::RECURRENCE_NONE) {
                    return false;
                }
                $intervalValue = '+' . $interval . ' years';
                break;
            default:
                $intervalValue = false;
        }
        return $intervalValue;
    }

    /**
     * Add recurrence items
     *
     * @param array         $times
     * @param Configuration $configuration
     * @param array         $baseEntry
     */
    protected function addRecurrenceItems(array &$times, Configuration $configuration, array $baseEntry)
    {
        if ($configuration->getRecurrence() === Configuration::RECURRENCE_NONE || $configuration->getDay() === Configuration::DAY_NONE) {
            return;
        }

        $recurrenceService = GeneralUtility::makeInstance(RecurrenceService::class);
        $amountCounter = $configuration->getCounterAmount();
        $tillDate = $configuration->getTillDate();
        $maxLimit = $this->getFrequencyLimitPerItem();
        $lastLoop = $baseEntry;
        for ($i = 0; $i < $maxLimit && ($amountCounter === 0 || $i < $amountCounter); $i++) {
            $loopEntry = $lastLoop;

            $dateTime = false;
            if ($configuration->getFrequency() === Configuration::FREQUENCY_MONTHLY) {
                $dateTime = $recurrenceService->getRecurrenceForNextMonth(
                    $loopEntry['start_date'],
                    $configuration->getRecurrence(),
                    $configuration->getDay()
                );
            } elseif ($configuration->getFrequency() === Configuration::FREQUENCY_YEARLY) {
                $dateTime = $recurrenceService->getRecurrenceForNextYear(
                    $loopEntry['start_date'],
                    $configuration->getRecurrence(),
                    $configuration->getDay()
                );
            }
            if ($dateTime === false) {
                break;
            }

            /** @var \DateInterval $interval */
            $interval = $loopEntry['start_date']->diff($dateTime);
            $frequencyIncrement = $interval->format('%R%a days');

            $loopEntry = $this->createNextLoopEntry($loopEntry, $frequencyIncrement);

            if ($tillDate instanceof \DateTimeInterface && $loopEntry['start_date'] > $tillDate) {
                break;
            }

            $lastLoop = $loopEntry;
            $times[] = $loopEntry;
        }
    }

    /**
     * Get the limit of the frequency
     *
     * @return int
     */
    protected function getFrequencyLimitPerItem()
    {
        $maxLimit = (int)ConfigurationUtility::get('frequencyLimitPerItem');
        if ($maxLimit <= 0) {
            $maxLimit = 300;
        }
        return $maxLimit;
    }
}
