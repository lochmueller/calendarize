<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Ical;

/**
 * Provides the methods to hydrate a Configuration object.
 * Used to create a Configuration based on an iCalendar VEVENT.
 *
 * Interface EventConfigurationInterface
 */
interface EventConfigurationInterface
{
    /**
     * Value for starTime if the event is allDay.
     */
    public const ALLDAY_START_TIME = 0;

    /**
     * Value for endTime if the event is allDay.
     */
    public const ALLDAY_END_TIME = 0;

    /**
     * Get the start date.
     * The date is converted to the local timezone and set to the beginning of the day.
     */
    public function getStartDate(): ?\DateTime;

    /**
     * Get the inclusive end date.
     * The date is converted to the local timezone and set to the beginning of the day.
     */
    public function getEndDate(): ?\DateTime;

    /**
     * Get start time.
     * The time is calculated in the local timezone.
     */
    public function getStartTime(): int;

    /**
     * Get end time.
     * The time is calculated in the local timezone.
     */
    public function getEndTime(): int;

    /**
     * Get allDay.
     *
     * The "VEVENT" is also the calendar component used to specify an
     * anniversary or daily reminder within a calendar. These events
     * have a DATE value type for the "DTSTART" property instead of the
     * default value type of DATE-TIME. If such a "VEVENT" has a "DTEND"
     * property, it MUST be specified as a DATE value also.
     */
    public function isAllDay(): bool;

    /**
     * Get openEndTime.
     */
    public function isOpenEndTime(): bool;

    /**
     * Get state.
     */
    public function getState(): string;

    /**
     * Get repeating rules.
     */
    public function getRRule(): array;
}
