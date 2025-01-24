<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Event;

use HDNET\Calendarize\Ical\ICalEvent;
use Psr\EventDispatcher\StoppableEventInterface;

final class ImportSingleIcalEvent implements StoppableEventInterface
{
    private bool $stopped = false;

    public function __construct(
        private readonly ICalEvent $event,
        private readonly int $pid,
    ) {}

    public function getEvent(): ICalEvent
    {
        return $this->event;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function stop(): void
    {
        $this->stopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
}
