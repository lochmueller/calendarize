<?php

/**
 * Provide strftime function in UTC context.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use HDNET\Calendarize\ViewHelpers\AbstractViewHelper;

/**
 * Check if the given Index is on the given day.
 */
class FormatUtcDateViewHelper extends AbstractViewHelper
{
    /**
     * Format dateTime using strftime() with UTC timezone
     *
     * @param \DateTime $date
     * @param string     $format
     *
     * @return string
     */
    public function render(\DateTime $date, string $format = '')
    {
        // save configured timezone
        $timezone = date_default_timezone_get();
        // set timezone to UTC
        date_default_timezone_set('UTC');

        $result = strftime($format, (int) $date->format('U'));

        // restore timezone setting
        date_default_timezone_set($timezone);

        return $result;
    }
}
