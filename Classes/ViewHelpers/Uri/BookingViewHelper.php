<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

use HDNET\Calendarize\ViewHelpers\Link\BookingViewHelper as BaseBookingViewHelper;

/**
 * Uri to the booking.
 */
class BookingViewHelper extends BaseBookingViewHelper
{
    /**
     * Render the uri to the given booking.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
