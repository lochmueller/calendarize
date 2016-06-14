<?php

/**
 * Booking interface
 */

namespace HDNET\Calendarize\Features;

/**
 * Booking interface
 */
interface BookingInterface
{
    /**
     * Is the given event is bookable
     *
     * @return boolean
     */
    public function isBookable();
}
