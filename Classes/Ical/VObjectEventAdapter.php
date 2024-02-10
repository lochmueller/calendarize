<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Ical;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Utility\DateTimeUtility;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Property;

class VObjectEventAdapter implements ICalEvent
{
    protected VEvent $event;

    /**
     * VObjectEvent constructor.
     */
    public function __construct(VEvent $event)
    {
        $this->event = $event;
    }

    public function getEvent(): VEvent
    {
        return $this->event;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawData(): array
    {
        $raw = [];
        foreach ($this->event->children() as $child) {
            if ($child instanceof Property) {
                $raw[$child->name][] = $child->getValue();
            }
        }

        return $raw;
    }

    /**
     * {@inheritdoc}
     */
    public function getUid(): string
    {
        return $this->event->UID->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): ?string
    {
        if (!isset($this->event->SUMMARY)) {
            return null;
        }

        return $this->event->SUMMARY->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): ?string
    {
        if (!isset($this->event->DESCRIPTION)) {
            return null;
        }

        return $this->event->DESCRIPTION->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getLocation(): ?string
    {
        if (!isset($this->event->LOCATION)) {
            return null;
        }

        return $this->event->LOCATION->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizer(): ?string
    {
        if (!isset($this->event->ORGANIZER)) {
            return null;
        }

        return $this->event->ORGANIZER->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDate(): ?\DateTime
    {
        if (!isset($this->event->DTSTART)) {
            return null;
        }
        $start = $this->event->DTSTART->getDateTime();

        if ($this->isAllDay()) {
            // Allows the date to be reparsed with the right timezone.
            $start = $start->format('Y-m-d');
        }

        return DateTimeUtility::getDayStart($start);
    }

    /**
     * {@inheritdoc}
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
            // Allows the date to be reparsed with the right timezone.
            $end = $end->format('Y-m-d');
        }

        return DateTimeUtility::getDayStart($end);
    }

    /**
     * Gets the end datetime, determines it with the duration or returns null.
     *
     * @return \DateTimeImmutable|null
     */
    protected function getEndDateTime(): ?\DateTimeImmutable
    {
        if (isset($this->event->DTEND)) {
            /** @var Property\ICalendar\DateTime $dtEnd */
            $dtEnd = $this->event->DTEND;

            return $dtEnd->getDateTime();
        }
        if (isset($this->event->DURATION)) {
            /** @var Property\ICalendar\DateTime $dtStart */
            $dtStart = $this->event->DTSTART;
            $duration = $this->event->DURATION->getDateInterval();

            return (clone $dtStart)->getDateTime()->add($duration);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartTime(): int
    {
        if ($this->isAllDay() || !isset($this->event->DTSTART)) {
            return self::ALLDAY_START_TIME;
        }

        /** @var Property\ICalendar\DateTime $start */
        $start = $this->event->DTSTART;

        return DateTimeUtility::getNormalizedDaySecondsOfDateTime($start->getDateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function getEndTime(): int
    {
        $end = $this->getEndDateTime();
        if ($this->isAllDay() || empty($end)) {
            return self::ALLDAY_END_TIME;
        }

        return DateTimeUtility::getNormalizedDaySecondsOfDateTime($end);
    }

    /**
     * {@inheritdoc}
     */
    public function isAllDay(): bool
    {
        if (!isset($this->event->DTSTART)) {
            return true;
        }
        /** @var Property\ICalendar\DateTime $start */
        $start = $this->event->DTSTART;

        return !$start->hasTime();
    }

    /**
     * {@inheritdoc}
     */
    public function isOpenEndTime(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        if (isset($this->event->STATUS)) {
            $status = $this->event->STATUS->getValue();
            if ('CANCELLED' === $status) {
                return ConfigurationInterface::STATE_CANCELED;
            }
        }

        return ConfigurationInterface::STATE_DEFAULT;
    }

    /**
     * {@inheritdoc}
     */
    public function getRRule(): array
    {
        if (!isset($this->event->RRULE)) {
            return [];
        }
        $rrule = $this->event->RRULE->getValue();
        if (\is_string($rrule)) {
            $rrule = Property\ICalendar\Recur::stringToArray($rrule);
        }

        return $rrule;
    }
}
