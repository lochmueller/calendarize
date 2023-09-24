<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

use HDNET\Calendarize\ViewHelpers\Link\QuarterViewHelper as BaseQuarterViewHelper;

/**
 * Uri to the quarter.
 */
class QuarterViewHelper extends BaseQuarterViewHelper
{
    /**
     * Render the uri to the given quarter.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
