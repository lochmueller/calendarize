<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Ical;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Utility\DateTimeUtility;

/**
 * Class DissectEvent
 */
class DissectEventAdapter implements ICalEvent
{

    /**
     * @var \JMBTechnologyLimited\ICalDissect\ICalEvent
     */
    protected $event;

    /**
     * DissectEvent constructor.
     * @param \JMBTechnologyLimited\ICalDissect\ICalEvent $event
     */
    public function __construct(\JMBTechnologyLimited\ICalDissect\ICalEvent $event)
    {
        $this->event = $event;
    }

    /**
     * @return array
     */
    public function getRawData(): array
    {
        return $this->event->getRaw();
    }

    /**
     * @return \JMBTechnologyLimited\ICalDissect\ICalEvent
     */
    public function getEvent(): \JMBTechnologyLimited\ICalDissect\ICalEvent
    {
        return $this->event;
    }

    /**
     * @inheritDoc
     */
    public function getUid(): string
    {
        return $this->event->getUid();
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): ?string
    {
        return $this->event->getSummary();
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): ?string
    {
        return $this->event->getDescription();
    }

    /**
     * @inheritDoc
     */
    public function getLocation(): ?string
    {
        return $this->event->getLocation();
    }

    /**
     * @inheritDoc
     */
    public function getOrganizer(): ?string
    {
        return $this->getEvent()->getRaw('ORGANIZER')[0] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getStartDate(): ?\DateTime
    {
        if (empty($this->event->getStart())) {
            return null;
        }
        return DateTimeUtility::getDayStart($this->event->getStart());
    }

    /**
     * @inheritDoc
     */
    public function getStartTime(): int
    {
        if ($this->isAllDay() || empty($this->event->getStart())) {
            return self::ALLDAY_START_TIME;
        }
        return DateTimeUtility::getDaySecondsOfDateTime($this->event->getStart());
    }

    /**
     * @inheritDoc
     */
    public function getEndDate(): ?\DateTime
    {
        $endDate = $this->event->getEnd();
        if (empty($endDate)) {
            return null;
        }
        if ($this->isAllDay()) {
            // Converts the exclusive enddate to inclusive
            $endDate = (clone $endDate)->sub(new \DateInterval('P1D'));
        }

        return DateTimeUtility::getDayStart($endDate);
    }

    /**
     * @inheritDoc
     */
    public function getEndTime(): int
    {
        if ($this->isAllDay() || empty($this->event->getEnd())) {
            return self::ALLDAY_END_TIME;
        }
        return DateTimeUtility::getDaySecondsOfDateTime($this->event->getEnd());
    }

    /**
     * @inheritDoc
     */
    public function isAllDay(): bool
    {
        // The ICalDissect\ICalEvent does not provide information,
        // if DTSTART is a DATE or DATE-TIME
        // If no time was given, the class sets following values
        //  starTime to 00:00:00 = 0
        //  endTime to 23:59:59 = (24h*60m*60s - 1) = 86399
        // So we check for these values.
        // Note: By spec (RFC) DTEND is irrelevant for allday events,
        //       so this may produce invalid results.
        $start = true;
        $end = true;
        if (!empty($this->event->getStart())) {
            $start = DateTimeUtility::getDaySecondsOfDateTime($this->event->getStart()) === 0;
        }
        if (!empty($this->event->getEnd())) {
            $end = DateTimeUtility::getDaySecondsOfDateTime($this->event->getEnd()) === 86399;
        }
        return $start && $end;
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
        if ($this->event->isDeleted()) {
            return ConfigurationInterface::STATE_CANCELED;
        }
        return ConfigurationInterface::STATE_DEFAULT;
    }
}
