<?php

/**
 * Uri to the day.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the day.
 */
class DayViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\DayViewHelper
{
    /**
     * Render the uri to the given day.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
