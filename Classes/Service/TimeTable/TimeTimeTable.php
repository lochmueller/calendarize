<?php

/**
 * Time service.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Service\RecurrenceService;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Time service.
 */
class TimeTimeTable extends AbstractTimeTable
{
    /**
     * Modify the given times via the configuration.
     *
     * @param array         $times
     * @param Configuration $configuration
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
            'end_time' => 0 === $endTime ? self::DAY_END : $endTime,
            'all_day' => $configuration->isAllDay(),
            'state' => $configuration->getState(),
        ];
        if (!$this->validateBaseEntry($baseEntry)) {
            return;
        }
        $times[$this->calculateEntryKey($baseEntry)] = $baseEntry;
        $this->addFrequencyItems($times, $configuration, $baseEntry);
        $this->addRecurrenceItems($times, $configuration, $baseEntry);
        $this->respectDynamicEndDates($times, $configuration);
    }

    /**
     * Respect the selection of dynamic enddates.
     *
     * @param array         $times
     * @param Configuration $configuration
     */
    protected function respectDynamicEndDates(array &$times, Configuration $configuration)
    {
        switch ($configuration->getEndDateDynamic()) {
            case Configuration::END_DYNAMIC_1_DAY:
                $callback = function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('+1 day');
                    }

                    return $entry;
                };
                break;
            case Configuration::END_DYNAMIC_1_WEEK:
                $callback = function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('+1 week');
                    }

                    return $entry;
                };
                break;
            case Configuration::END_DYNAMIC_END_WEEK:
                $callback = function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('monday next week');
                        $entry['end_date']->modify('-1 day');
                    }

                    return $entry;
                };
                break;
            case Configuration::END_DYNAMIC_END_MONTH:
                $callback = function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('last day of this month');
                    }

                    return $entry;
                };
                break;
            case Configuration::END_DYNAMIC_END_YEAR:

                $callback = function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->setDate((int) $entry['end_date']->format('Y'), 12, 31);
                    }

                    return $entry;
                };
                break;
        }

        if (!isset($callback)) {
            return;
        }

        $new = [];
        foreach ($times as $hash => $record) {
            $target = $callback($record);
            $new[$this->calculateEntryKey($target)] = $target;
        }
        $times = $new;
    }

    /**
     * Validate the base entry, if there are logica mistakes.
     *
     * @param array $baseEntry
     *
     * @return bool
     */
    protected function validateBaseEntry(array $baseEntry): bool
    {
        $message = null;
        if (!($baseEntry['start_date'] instanceof \DateTimeInterface)) {
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                'There is no usage for a event configuration without start date?!',
                'No start date?',
                FlashMessage::ERROR
            );
        } elseif ($baseEntry['end_date'] instanceof \DateTimeInterface && $baseEntry['start_date'] > $baseEntry['end_date']) {
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
        } elseif ($baseEntry['end_date'] instanceof \DateTimeInterface && !$baseEntry['all_day'] && $baseEntry['start_date']->format('d.m.Y') === $baseEntry['end_date']->format('d.m.Y') && $baseEntry['start_time'] % DateTimeUtility::SECONDS_DAY > $baseEntry['end_time'] % DateTimeUtility::SECONDS_DAY) {
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

            return false;
        }

        return true;
    }

    /**
     * Add frequency items.
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
        for ($i = 0; $i < $maxLimit && (0 === $amountCounter || $i < $amountCounter); ++$i) {
            $loopEntry = $this->createNextLoopEntry($lastLoop, $frequencyIncrement);

            if ($tillDate instanceof \DateTimeInterface && $loopEntry['start_date'] > $tillDate) {
                break;
            }

            $lastLoop = $loopEntry;
            $times[$this->calculateEntryKey($loopEntry)] = $loopEntry;
        }
    }

    /**
     * Create the next loop entry.
     *
     * @param array  $loopEntry
     * @param string $modification
     *
     * @return array
     */
    protected function createNextLoopEntry(array $loopEntry, string $modification): array
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
     * Get the frequency date increment.
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
                if (Configuration::RECURRENCE_NONE !== $configuration->getRecurrence()) {
                    return false;
                }
                $intervalValue = '+' . $interval . ' months';
                break;
            case Configuration::FREQUENCY_YEARLY:
                if (Configuration::RECURRENCE_NONE !== $configuration->getRecurrence()) {
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
     * Add recurrence items.
     *
     * @param array         $times
     * @param Configuration $configuration
     * @param array         $baseEntry
     */
    protected function addRecurrenceItems(array &$times, Configuration $configuration, array $baseEntry)
    {
        if (Configuration::RECURRENCE_NONE === $configuration->getRecurrence() || Configuration::DAY_NONE === $configuration->getDay()) {
            return;
        }

        $recurrenceService = GeneralUtility::makeInstance(RecurrenceService::class);
        $amountCounter = $configuration->getCounterAmount();
        $tillDate = $configuration->getTillDate();
        $maxLimit = $this->getFrequencyLimitPerItem();
        $lastLoop = $baseEntry;
        for ($i = 0; $i < $maxLimit && (0 === $amountCounter || $i < $amountCounter); ++$i) {
            $loopEntry = $lastLoop;

            $dateTime = false;
            if (Configuration::FREQUENCY_MONTHLY === $configuration->getFrequency()) {
                $dateTime = $recurrenceService->getRecurrenceForNextMonth(
                    $loopEntry['start_date'],
                    $configuration->getRecurrence(),
                    $configuration->getDay()
                );
            } elseif (Configuration::FREQUENCY_YEARLY === $configuration->getFrequency()) {
                $dateTime = $recurrenceService->getRecurrenceForNextYear(
                    $loopEntry['start_date'],
                    $configuration->getRecurrence(),
                    $configuration->getDay()
                );
            }
            if (false === $dateTime) {
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
            $times[$this->calculateEntryKey($loopEntry)] = $loopEntry;
        }
    }

    /**
     * Get the limit of the frequency.
     *
     * @return int
     */
    protected function getFrequencyLimitPerItem(): int
    {
        $maxLimit = (int) ConfigurationUtility::get('frequencyLimitPerItem');
        if ($maxLimit <= 0) {
            $maxLimit = 300;
        }

        return $maxLimit;
    }
}
