<?php

/**
 * Uri to the year.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the year.
 */
class YearViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\YearViewHelper
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
