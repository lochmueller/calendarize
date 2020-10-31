<?php

/**
 * Provide strftime function in UTC context.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use TYPO3\CMS\Fluid\ViewHelpers\Format\DateViewHelper;

/**
 * Formats the date to UTC.
 */
class FormatUtcDateViewHelper extends DateViewHelper
{
    /**
     * Format dateTime to the UTC timezone.
     *
     * @return string
     */
    public function render()
    {
        // save configured timezone
        $timezone = date_default_timezone_get();
        // set timezone to UTC
        date_default_timezone_set('UTC');

        $result = parent::render();

        // restore timezone setting
        date_default_timezone_set($timezone);

        return $result;
    }
}
