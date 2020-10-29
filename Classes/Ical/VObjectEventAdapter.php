<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Ical;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Utility\DateTimeUtility;
use Sabre\VObject\Component\VEvent;

class VObjectEventAdapter implements ICalEvent
{

    /**
     * @var VEvent
     */
    protected $event;

    /**
     * VObjectEvent constructor.
     * @param VEvent $event
     */
    public function __construct(VEvent $event)
    {
        $this->event = $event;
    }

    /**
     * @return VEvent
     */
    public function getEvent(): VEvent
    {
        return $this->event;
    }

    /**
     * @inheritDoc
     */
    public function getRawData(): array
    {
        return $this->event->children();
    }

    /**
     * @inheritDoc
     */
    public function getUid(): string
    {
        return $this->event->UID->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): ?string
    {
        if (!isset($this->event->SUMMARY)) {
            return null;
        }
        return $this->event->SUMMARY->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): ?string
    {
        if (!isset($this->event->DESCRIPTION)) {
            return null;
        }

        return $this->event->DESCRIPTION->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getLocation(): ?string
    {
        if (!isset($this->event->LOCATION)) {
            return null;
        }

        return $this->event->LOCATION->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getOrganizer(): ?string
    {
        if (!isset($this->event->ORGANIZER)) {
            return null;
        }

        return $this->event->ORGANIZER->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getStartDate(): ?\DateTime
    {
        if (!isset($this->event->DTSTART)) {
            return null;
        }

        /** @var \Sabre\VObject\Property\ICalendar\DateTime $start */
        $start = $this->event->DTSTART;
        return DateTimeUtility::getDayStart($start->getDateTime());
    }

    /**
     * @inheritDoc
     */
    public function getEndDate(): ?\DateTime
    {
        $end = $this->getEndDateTime();
        if (empty($end)) {
            return null;
        }

        if ($this->isAllDay()) {
            // Converts the exclusive enddate to inclusive
            $end = (clone $end)->sub(new \DateInterval('P1D'));
        }

        return DateTimeUtility::getDayStart($end);
    }

    /**
     * Gets the end datetime, determines it with the duration or returns null.
     * @return \DateTimeImmutable|null
     */
    protected function getEndDateTime(): ?\DateTimeImmutable
    {
        if (isset($this->event->DTEND)) {
            /** @var \Sabre\VObject\Property\ICalendar\DateTime $dtEnd */
            $dtEnd = $this->event->DTEND;

            return $dtEnd->getDateTime();
        }
        if (isset($this->event->DURATION)) {
            /** @var \Sabre\VObject\Property\ICalendar\DateTime $dtStart */
            $dtStart = $this->event->DTSTART;
            $duration = $this->event->DURATION->getDateInterval();

            return (clone $dtStart)->getDateTime()->add($duration);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getStartTime(): int
    {
        if ($this->isAllDay() || !isset($this->event->DTSTART)) {
            return self::ALLDAY_START_TIME;
        }

        /** @var \Sabre\VObject\Property\ICalendar\DateTime $start */
        $start = $this->event->DTSTART;
        return DateTimeUtility::getDaySecondsOfDateTime($start->getDateTime());
    }

    /**
     * @inheritDoc
     */
    public function getEndTime(): int
    {
        $end = $this->getEndDateTime();
        if ($this->isAllDay() || empty($end)) {
            return self::ALLDAY_END_TIME;
        }

        return DateTimeUtility::getDaySecondsOfDateTime($end);
    }

    /**
     * @inheritDoc
     */
    public function isAllDay(): bool
    {
        if (!isset($this->event->DTSTART)) {
            return true;
        }
        /** @var \Sabre\VObject\Property\ICalendar\DateTime $start */
        $start = $this->event->DTSTART;
        return !$start->hasTime();
    }

    /**
     * @inheritDoc
     */
    public function isOpenEndTime(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getState(): string
    {
        if (isset($this->event->STATUS)) {
            $status = $this->event->STATUS->getValue();
            if ($status === 'CANCELLED') {
                return ConfigurationInterface::STATE_CANCELED;
            }
        }
        return ConfigurationInterface::STATE_DEFAULT;
    }
}
