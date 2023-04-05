<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

use HDNET\Calendarize\ViewHelpers\Link\MonthViewHelper as BaseMonthViewHelper;

/**
 * Uri to the month.
 */
class MonthViewHelper extends BaseMonthViewHelper
{
    /**
     * Render the uri to the given month.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
