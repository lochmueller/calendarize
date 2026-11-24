<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Event;

/**
 * Dispatched once before the single ICal events of an import are processed.
 */
final class ImportStartEvent
{
    public function __construct(
        private readonly string $icsCalendarUri,
        private readonly int $pid,
        private readonly bool $disableNotImported = false,
    ) {}

    public function getIcsCalendarUri(): string
    {
        return $this->icsCalendarUri;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * Should all previously imported events on the target page be disabled,
     * so that only the events of the current import are active afterwards?
     */
    public function isDisableNotImported(): bool
    {
        return $this->disableNotImported;
    }
}
