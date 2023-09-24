<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Ical;

interface ICalEvent extends EventConfigurationInterface
{
    /**
     * Returns event data as key value array.
     */
    public function getRawData(): array;

    /**
     * Get UID.
     */
    public function getUid(): string;

    /**
     * Get title.
     */
    public function getTitle(): ?string;

    /**
     * Get description.
     */
    public function getDescription(): ?string;

    /**
     * Get location.
     */
    public function getLocation(): ?string;

    /**
     * Get organizer.
     */
    public function getOrganizer(): ?string;
}
