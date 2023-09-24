<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

use HDNET\Calendarize\ViewHelpers\Link\WeekViewHelper as BaseWeekViewHelper;

/**
 * Uri to the week.
 */
class WeekViewHelper extends BaseWeekViewHelper
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
