<?php

/**
 * Uri to the week.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the week.
 */
class WeekViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\WeekViewHelper
{
    /**
     * Render the uri to the given week.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
