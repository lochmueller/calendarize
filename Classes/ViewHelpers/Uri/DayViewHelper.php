<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

use HDNET\Calendarize\ViewHelpers\Link\DayViewHelper as BaseDayViewHelper;

/**
 * Uri to the day.
 */
class DayViewHelper extends BaseDayViewHelper
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
