<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Features;

/**
 * Booking interface.
 */
interface BookingInterface
{
    /**
     * If the given event is bookable.
     */
    public function isBookable(): bool;
}
