<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Event;

use HDNET\Calendarize\Ical\ICalEvent;

final class ImportSingleIcalEvent
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
     * ImportSingleEvent constructor.
     * @param ICalEvent $event
     * @param int $pid
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
}
