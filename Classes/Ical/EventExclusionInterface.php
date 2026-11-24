<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Ical;

/**
 * Optional interface for imported events, that know occurrences of their recurrence,
 * which should not be part of the time table (EXDATE or overridden via RECURRENCE-ID).
 */
interface EventExclusionInterface
{
    /**
     * Get the start of all occurrences, that should be removed from the recurrence.
     *
     * @return \DateTimeInterface[]
     */
    public function getExcludedDates(): array;
}
