<?php

/**
 * Booking interface.
 */

namespace HDNET\Calendarize\Features;

/**
 * Booking interface.
 */
interface BookingInterface
{
    /**
     * Is the given event is bookable.
     *
     * @return bool
     */
    public function isBookable();
}
