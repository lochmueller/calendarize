<?php

/**
 * Uri to the booking.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the booking.
 */
class BookingViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\BookingViewHelper
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
