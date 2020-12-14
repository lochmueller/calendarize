<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Ical;

interface ICalEvent extends EventConfigurationInterface
{
    /**
     * Returns event data as key value array.
     *
     * @return array
     */
    public function getRawData(): array;

    /**
     * Get UID.
     *
     * @return string
     */
    public function getUid(): string;

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle(): ?string;

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Get location.
     *
     * @return string|null
     */
    public function getLocation(): ?string;

    /**
     * Get organinzer.
     *
     * @return string|null
     */
    public function getOrganizer(): ?string;
}
