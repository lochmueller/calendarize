<?php

/**
 * Booking interface.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Features;

/**
 * Booking interface.
 */
interface BookingInterface
{
    /**
     * Is the given event is bookable.
     */
    public function isBookable(): bool;
}
