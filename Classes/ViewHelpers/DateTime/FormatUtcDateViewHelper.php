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
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('date', \DateTimeInterface::class, 'DateTimeInterface Object to format', true);
        $this->registerArgument('format', 'string', 'format passed to strftime', false, '');
    }

    /**
     * Format dateTime using strftime() with UTC timezone.
     *
     * @return string
     */
    public function render()
    {
        // save configured timezone
        $timezone = \date_default_timezone_get();
        // set timezone to UTC
        \date_default_timezone_set('UTC');

        $result = \strftime($this->arguments['format'], (int)$this->arguments['date']->format('U'));

        // restore timezone setting
        \date_default_timezone_set($timezone);

        return $result;
    }
}
