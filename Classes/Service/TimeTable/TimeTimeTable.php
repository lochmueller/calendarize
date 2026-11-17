<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Service\RecurrenceService;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Time service.
 */
class TimeTimeTable extends AbstractTimeTable
{
    /**
     * Frequencies that support the day field as a filter.
     */
    protected const DAY_FILTER_FREQUENCIES = [
        ConfigurationInterface::FREQUENCY_MINUTELY,
        ConfigurationInterface::FREQUENCY_HOURLY,
        ConfigurationInterface::FREQUENCY_DAILY,
    ];

    /**
     * Modify the given times via the configuration.
     *
     * @param array         $times
     * @param Configuration $configuration
     */
    public function handleConfiguration(array &$times, Configuration $configuration): void
    {
        $startTime = $configuration->isAllDay() ? null : $configuration->getStartTime();
        $endTime = $configuration->isAllDay() ? null : $configuration->getEndTime();
        $baseEntry = [
            'pid' => $configuration->getPid(),
            'start_date' => $configuration->getStartDate(),
            'end_date' => $configuration->getEndDate() ?: $configuration->getStartDate(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'all_day' => $configuration->isAllDay(),
            'open_end_time' => $configuration->isOpenEndTime(),
            'state' => $configuration->getState(),
        ];
        if (!$this->validateBaseEntry($baseEntry)) {
            return;
        }
        $entriesBefore = \count($times);
        $dayFilter = $this->getDayFilter($configuration);
        // The base entry is only part of the time table, if it matches the day filter
        if ($this->matchesDayFilter($baseEntry, $dayFilter)) {
            $times[$this->calculateEntryKey($baseEntry)] = $baseEntry;
        }
        $tillDateConfiguration = $this->getTillDateConfiguration($configuration, $baseEntry);
        $this->addFrequencyItems($times, $configuration, $baseEntry, $tillDateConfiguration);
        $this->addRecurrenceItems($times, $configuration, $baseEntry, $tillDateConfiguration);
        $this->respectDynamicEndDates($times, $configuration);
        $this->removeBaseEntryIfNecessary($times, $configuration, $baseEntry, $tillDateConfiguration);

        if ([] !== $dayFilter && \count($times) === $entriesBefore) {
            HelperUtility::createTranslatedFlashMessage(
                'flashMessage.dayFilterNoResult.text',
                'flashMessage.dayFilterNoResult.title',
                ContextualFeedbackSeverity::WARNING,
            );
        }
    }

    /**
     * Get the weekdays (date format 'N') the generated items are limited to.
     *
     * In contrast to the monthly/yearly frequencies - where the day is the anchor of the
     * recurrence (e.g. "first monday of the month") - the day acts as a filter for the
     * day based frequencies. This matches the BYDAY "limit" behaviour of RFC 5545.
     *
     * @return array<int> empty, if no filter is configured
     */
    protected function getDayFilter(Configuration $configuration): array
    {
        if (ConfigurationInterface::DAY_NONE === $configuration->getDay()) {
            return [];
        }

        if (!\in_array($configuration->getFrequency(), self::DAY_FILTER_FREQUENCIES, true)) {
            return [];
        }

        /** @var RecurrenceService $recurrenceService */
        $recurrenceService = GeneralUtility::makeInstance(RecurrenceService::class);

        return $recurrenceService->getValidDays($configuration->getDay());
    }

    /**
     * Check if the entry starts on one of the given weekdays.
     *
     * @param array<int> $dayFilter
     */
    protected function matchesDayFilter(array $entry, array $dayFilter): bool
    {
        if ([] === $dayFilter || !($entry['start_date'] instanceof \DateTimeInterface)) {
            return true;
        }

        return \in_array((int)$entry['start_date']->format('N'), $dayFilter, true);
    }

    /**
     * Respect the selection of dynamic enddates.
     */
    protected function respectDynamicEndDates(array &$times, Configuration $configuration): void
    {
        switch ($configuration->getEndDateDynamic()) {
            case ConfigurationInterface::END_DYNAMIC_1_DAY:
                $callback = static function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('+1 day');
                    }

                    return $entry;
                };
                break;
            case ConfigurationInterface::END_DYNAMIC_1_WEEK:
                $callback = static function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('+1 week');
                    }

                    return $entry;
                };
                break;
            case ConfigurationInterface::END_DYNAMIC_END_WEEK:
                $callback = static function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('monday next week');
                        $entry['end_date']->modify('-1 day');
                    }

                    return $entry;
                };
                break;
            case ConfigurationInterface::END_DYNAMIC_END_MONTH:
                $callback = static function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('last day of this month');
                    }

                    return $entry;
                };
                break;
            case ConfigurationInterface::END_DYNAMIC_END_YEAR:
                $callback = static function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->setDate((int)$entry['end_date']->format('Y'), 12, 31);
                    }

                    return $entry;
                };
                break;
        }

        if (!isset($callback)) {
            return;
        }

        $new = [];
        foreach ($times as $record) {
            $target = $callback($record);
            $new[$this->calculateEntryKey($target)] = $target;
        }
        $times = $new;
    }

    /**
     * Validate the base entry, if there are logical mistakes.
     */
    protected function validateBaseEntry(array $baseEntry): bool
    {
        // Invalid start date
        if (!($baseEntry['start_date'] instanceof \DateTimeInterface)) {
            HelperUtility::createTranslatedFlashMessage(
                'flashMessage.missingStartDate.text',
                'flashMessage.missingStartDate.title',
                ContextualFeedbackSeverity::ERROR,
            );

            return false;
        }

        // End date is before start date
        if ($baseEntry['end_date'] instanceof \DateTimeInterface && $baseEntry['start_date'] > $baseEntry['end_date']) {
            HelperUtility::createTranslatedFlashMessage(
                'wrong.date.message',
                'wrong.date',
                ContextualFeedbackSeverity::ERROR,
            );

            return false;
        }

        // End date is before start date considering time, all day and open end
        if (
            $baseEntry['end_date'] instanceof \DateTimeInterface
            && !$baseEntry['all_day']
            && !$baseEntry['open_end_time']
            && $baseEntry['start_date']->format('d.m.Y') === $baseEntry['end_date']->format('d.m.Y')
            && $baseEntry['start_time'] % DateTimeUtility::SECONDS_DAY
            > $baseEntry['end_time'] % DateTimeUtility::SECONDS_DAY
        ) {
            HelperUtility::createTranslatedFlashMessage(
                'wrong.time.message',
                'wrong.time',
                ContextualFeedbackSeverity::ERROR,
            );

            return false;
        }

        return true;
    }

    /**
     * Add frequency items.
     */
    protected function addFrequencyItems(
        array &$times,
        Configuration $configuration,
        array $baseEntry,
        array $tillDateConfiguration,
    ): void {
        $frequencyIncrement = $this->getFrequencyIncrement($configuration);
        if (!$frequencyIncrement) {
            return;
        }
        $dayFilter = $this->getDayFilter($configuration);
        $amountCounter = $configuration->getCounterAmount();
        $maxLimit = $this->getFrequencyLimitPerItem();
        $lastLoop = $baseEntry;
        $loopEntriesAdded = 0;
        $repetitions = 0;
        $skippedDays = [];
        while ($loopEntriesAdded < $maxLimit && (0 === $amountCounter || $repetitions < $amountCounter)) {
            $loopEntry = $this->createNextLoopEntry($lastLoop, $frequencyIncrement);

            if (
                $tillDateConfiguration['tillDate'] instanceof \DateTimeInterface
                && $loopEntry['start_date'] > $tillDateConfiguration['tillDate']
            ) {
                break;
            }

            $lastLoop = $loopEntry;

            // Filtered days are no repetitions at all (RFC 5545 counts the remaining occurrences)
            if (!$this->matchesDayFilter($loopEntry, $dayFilter)) {
                $skippedDays[$loopEntry['start_date']->format('Y-m-d')] = true;
                // If seven different days in a row do not match, the increment (e.g. every
                // seventh day) can never reach one of the configured weekdays.
                if (\count($skippedDays) > 7) {
                    break;
                }

                continue;
            }

            $skippedDays = [];
            ++$repetitions;

            if (
                $tillDateConfiguration['tillDatePast'] instanceof \DateTimeInterface
                && $loopEntry['end_date'] < $tillDateConfiguration['tillDatePast']
            ) {
                continue;
            }

            $times[$this->calculateEntryKey($loopEntry)] = $loopEntry;
            ++$loopEntriesAdded;
        }
    }

    /**
     * Create the next loop entry.
     */
    protected function createNextLoopEntry(array $loopEntry, string $modification): array
    {
        // Time modification
        if (str_contains($modification, 'minutes') || str_contains($modification, 'hours')) {
            $startTime = new \DateTime('@' . ($loopEntry['start_time'] ?? 0));
            $compareTime = new \DateTime('@' . ($loopEntry['start_time'] ?? 0));
            $startTime->modify($modification);
            $loopEntry['start_time'] = $startTime->getTimestamp();

            $endTime = new \DateTime('@' . ($loopEntry['end_time'] ?? 0));
            $endTime->modify($modification);
            $loopEntry['end_time'] = $endTime->getTimestamp();

            if ($startTime->format('Y-m-d') !== $compareTime->format('Y-m-d')) {
                /** @var $startDate \DateTime */
                $startDate = clone $loopEntry['start_date'];
                $startDate->modify('+1 day');
                $loopEntry['start_date'] = $startDate;

                /** @var $endDate \DateTime */
                $endDate = clone $loopEntry['end_date'];
                $endDate->modify('+1 day');
                $loopEntry['end_date'] = $endDate;
            }
        } else {
            /** @var $startDate \DateTime */
            $startDate = clone $loopEntry['start_date'];
            $startDate->modify($modification);
            $loopEntry['start_date'] = $startDate;

            /** @var $endDate \DateTime */
            $endDate = clone $loopEntry['end_date'];
            $endDate->modify($modification);
            $loopEntry['end_date'] = $endDate;
        }

        return $loopEntry;
    }

    /**
     * Get the frequency date increment.
     */
    protected function getFrequencyIncrement(Configuration $configuration): string
    {
        $interval = max($configuration->getCounterInterval(), 1);
        switch ($configuration->getFrequency()) {
            case ConfigurationInterface::FREQUENCY_MINUTELY:
                $intervalValue = '+' . $interval . ' minutes';
                break;
            case ConfigurationInterface::FREQUENCY_HOURLY:
                $intervalValue = '+' . $interval . ' hours';
                break;
            case ConfigurationInterface::FREQUENCY_DAILY:
                $intervalValue = '+' . $interval . ' days';
                break;
            case ConfigurationInterface::FREQUENCY_WEEKLY:
                $intervalValue = '+' . $interval . ' weeks';
                break;
            case ConfigurationInterface::FREQUENCY_MONTHLY:
                if (ConfigurationInterface::RECURRENCE_NONE !== $configuration->getRecurrence()) {
                    return '';
                }
                $intervalValue = '+' . $interval . ' months';
                break;
            case ConfigurationInterface::FREQUENCY_YEARLY:
                if (ConfigurationInterface::RECURRENCE_NONE !== $configuration->getRecurrence()) {
                    return '';
                }
                $intervalValue = '+' . $interval . ' years';
                break;
            default:
                $intervalValue = '';
        }

        return $intervalValue;
    }

    /**
     * Add recurrence items.
     */
    protected function addRecurrenceItems(
        array &$times,
        Configuration $configuration,
        array $baseEntry,
        array $tillDateConfiguration,
    ): void {
        if (
            ConfigurationInterface::RECURRENCE_NONE === $configuration->getRecurrence()
            || ConfigurationInterface::DAY_NONE === $configuration->getDay()
        ) {
            return;
        }

        /** @var RecurrenceService $recurrenceService */
        $recurrenceService = GeneralUtility::makeInstance(RecurrenceService::class);
        $amountCounter = $configuration->getCounterAmount();
        $maxLimit = $this->getFrequencyLimitPerItem();
        $lastLoop = $baseEntry;
        $loopEntriesAdded = 0;
        $intervalCounter = max($configuration->getCounterInterval(), 1);
        for ($i = 0; $loopEntriesAdded < $maxLimit && (0 === $amountCounter || $i < $amountCounter); ++$i) {
            $loopEntry = $lastLoop;

            $dateTime = false;
            if (ConfigurationInterface::FREQUENCY_MONTHLY === $configuration->getFrequency()) {
                $dateTime = $recurrenceService->getRecurrenceForNextMonth(
                    $loopEntry['start_date'],
                    $configuration->getRecurrence(),
                    $configuration->getDay(),
                    $intervalCounter,
                );
            } elseif (ConfigurationInterface::FREQUENCY_YEARLY === $configuration->getFrequency()) {
                $dateTime = $recurrenceService->getRecurrenceForNextYear(
                    $loopEntry['start_date'],
                    $configuration->getRecurrence(),
                    $configuration->getDay(),
                    $intervalCounter,
                );
            }
            if (false === $dateTime) {
                break;
            }

            /** @var \DateInterval $interval */
            $interval = $loopEntry['start_date']->diff($dateTime);
            $frequencyIncrement = $interval->format('%R%a days');

            $loopEntry = $this->createNextLoopEntry($loopEntry, $frequencyIncrement);

            if (
                $tillDateConfiguration['tillDate'] instanceof \DateTimeInterface
                && $loopEntry['start_date'] > $tillDateConfiguration['tillDate']
            ) {
                break;
            }

            $lastLoop = $loopEntry;

            if (
                $tillDateConfiguration['tillDatePast'] instanceof \DateTimeInterface
                && $loopEntry['end_date'] < $tillDateConfiguration['tillDatePast']
            ) {
                continue;
            }

            $times[$this->calculateEntryKey($loopEntry)] = $loopEntry;
            ++$loopEntriesAdded;
        }
    }

    /**
     * Remove the base entry if necessary.
     */
    protected function removeBaseEntryIfNecessary(
        array &$times,
        Configuration $configuration,
        array $baseEntry,
        array $tillDateConfiguration,
    ): void {
        $baseEntryKey = $this->calculateEntryKey($baseEntry);
        $tillDate = $configuration->getTillDate();

        if (!isset($times[$baseEntryKey])) {
            return;
        }

        // if the till date is set via the till day feature and if the base entry does not
        // match the till date condition remove it from times
        if (
            !$tillDate instanceof \DateTimeInterface
            && $tillDateConfiguration['tillDate'] instanceof \DateTimeInterface
            && $baseEntry['start_date'] > $tillDateConfiguration['tillDate']
        ) {
            unset($times[$baseEntryKey]);
        } elseif (
            $tillDateConfiguration['tillDatePast'] instanceof \DateTimeInterface
            && $baseEntry['end_date'] < $tillDateConfiguration['tillDatePast']
        ) {
            // till date past can only be set via the till date day feature, if the base entry
            // does not match the till date past condition remove it from times
            unset($times[$baseEntryKey]);
        }
    }

    protected function getTillDateConfiguration(Configuration $configuration, array $baseEntry): array
    {
        // get values from item configuration
        $tillDate = $configuration->getTillDate();
        $tillDays = $configuration->getTillDays();
        $tillDaysRelative = $configuration->isTillDaysRelative();
        $tillDaysPast = $configuration->getTillDaysPast();
        $tillDatePast = null;

        // if not set get values from extension configuration
        if (null === $tillDays && null === $tillDaysPast) {
            $tillDays = ConfigurationUtility::get('tillDays');
            $tillDays = MathUtility::canBeInterpretedAsInteger($tillDays) ? (int)$tillDays : null;
            $tillDaysPast = ConfigurationUtility::get('tillDaysPast');
            $tillDaysPast = MathUtility::canBeInterpretedAsInteger($tillDaysPast) ? (int)$tillDaysPast : null;
            $tillDaysRelative = (bool)ConfigurationUtility::get('tillDaysRelative');
        }

        // get base date for till tillDate and tillDatePast calculation
        /** @var \DateTime $tillDaysBaseDate */
        $tillDaysBaseDate = $baseEntry['start_date'];
        if ($tillDaysRelative) {
            $tillDaysBaseDate = DateTimeUtility::resetTime();
        }

        // get actual tillDate
        if (!$tillDate instanceof \DateTimeInterface && (\is_int($tillDays))) {
            --$tillDays; // - 1 day because we already take the current day into account
            $tillDate = clone $tillDaysBaseDate;
            $tillDate->modify('+' . $tillDays . ' day');
        }

        // get actual tillDatePast
        if (\is_int($tillDaysPast)) {
            $tillDatePast = clone $tillDaysBaseDate;
            $tillDatePast->modify('-' . $tillDaysPast . ' day');
        }

        return [
            'tillDate' => $tillDate,
            'tillDatePast' => $tillDatePast,
        ];
    }

    /**
     * Get the limit of the frequency.
     */
    protected function getFrequencyLimitPerItem(): int
    {
        $maxLimit = (int)ConfigurationUtility::get('frequencyLimitPerItem');
        if ($maxLimit <= 0) {
            $maxLimit = 300;
        }

        return $maxLimit;
    }
}
