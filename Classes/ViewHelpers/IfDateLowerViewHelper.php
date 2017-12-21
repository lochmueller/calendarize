<?php

/**
 * Check if a date is lower.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use HDNET\Calendarize\Utility\DateTimeUtility;

/**
 * Check if a date is lower.
 */
class IfDateLowerViewHelper extends AbstractViewHelper
{
    /**
     * Render the view helper.
     *
     * Note: You have to wrap this view helper in an f:if ViewHelper.
     * This VH just return a boolean evaluation value
     *
     * @param string|\DateTime $base
     * @param \DateTime        $check
     *
     * @return string
     */
    public function render($base, $check)
    {
        $base = DateTimeUtility::normalizeDateTimeSingle($base);
        $check = DateTimeUtility::normalizeDateTimeSingle($check);

        return $base > $check;
    }
}
