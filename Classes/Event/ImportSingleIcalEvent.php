<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Event;

use HDNET\Calendarize\Ical\ICalEvent;
use Psr\EventDispatcher\StoppableEventInterface;

final class ImportSingleIcalEvent implements StoppableEventInterface
{
    /**
     * @var ICalEvent
     */
    private $event;

    /**
     * @var int
     */
    private $pid;

    /**
     * @var bool
     */
    private $stopped = false;

    /**
     * ImportSingleEvent constructor.
     *
     * @param ICalEvent $event
     * @param int       $pid
     */
    public function __construct(ICalEvent $event, int $pid)
    {
        $this->event = $event;
        $this->pid = $pid;
    }

    /**
     * @return ICalEvent
     */
    public function getEvent(): ICalEvent
    {
        return $this->event;
    }

    /**
     * @return int
     */
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
