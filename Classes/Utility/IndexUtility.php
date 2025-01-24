<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use HDNET\Calendarize\Domain\Model\Index;

/**
 * Index utility.
 */
class IndexUtility
{
    /**
     * Check if the Index is part of the range.
     *
     * @see IndexRepository::addTimeFrameConstraints
     */
    public static function isIndexInRange(
        Index $index,
        \DateTimeInterface $rangeStart,
        \DateTimeInterface $rangeEnd,
    ): bool {
        $indexStart = $index->getStartDateComplete();
        $indexEnd = $index->getEndDateComplete();

        // start in
        if ($indexStart >= $rangeStart && $indexStart <= $rangeEnd) {
            return true;
        }

        // end in
        if ($indexEnd >= $rangeStart && $indexEnd <= $rangeEnd) {
            return true;
        }

        // around range
        if ($indexStart <= $rangeStart && $indexEnd >= $rangeEnd) {
            return true;
        }

        return false;
    }
}
