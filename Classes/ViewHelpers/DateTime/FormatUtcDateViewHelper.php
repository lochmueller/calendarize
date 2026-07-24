<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use HDNET\Calendarize\ViewHelpers\Format\DateViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Formats the date to UTC.
 */
class FormatUtcDateViewHelper extends DateViewHelper
{
    /**
     * Format dateTime to the UTC timezone.
     *
     * @return string
     *
     * @throws Exception
     */
    public function render(): string
    {
        // save configured timezone
        $timezone = date_default_timezone_get();
        // set timezone to UTC
        date_default_timezone_set('UTC');

        $date = $this->arguments['date'];
        if ($date instanceof \DateTimeInterface) {
            // Convert date to timestamp, so that the parent reparses it in the UTC timezone.
            $this->arguments['date'] = $date->getTimestamp();
        }

        $result = parent::render();

        // restore timezone setting
        date_default_timezone_set($timezone);

        return $result;
    }
}
