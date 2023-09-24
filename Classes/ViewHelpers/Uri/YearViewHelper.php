<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

use HDNET\Calendarize\ViewHelpers\Link\YearViewHelper as BaseYearViewHelper;

/**
 * Uri to the year.
 */
class YearViewHelper extends BaseYearViewHelper
{
    /**
     * Render the uri to the given year.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
