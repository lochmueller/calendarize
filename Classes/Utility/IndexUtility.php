<?php

/**
 * Index utility.
 */
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
     * @param Index              $index
     * @param \DateTimeInterface $rangeStart
     * @param \DateTimeInterface $rangeEnd
     *
     * @see IndexRepository::addTimeFrameConstraints
     *
     * @return bool
     */
    public static function isIndexInRange($index, \DateTimeInterface $rangeStart, \DateTimeInterface $rangeEnd)
    {
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
