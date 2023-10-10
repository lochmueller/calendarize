<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Configuration for time options.
 */
class Configuration extends AbstractModel implements ConfigurationInterface
{
    use ImportTrait;

    protected string $type = self::TYPE_TIME;

    protected string $handling = self::HANDLING_INCLUDE;

    protected string $state = self::STATE_DEFAULT;

    protected ?\DateTime $startDate = null;

    protected ?\DateTime $endDate = null;

    protected string $endDateDynamic = '';

    protected int $startTime = 0;

    protected int $endTime = 0;

    protected bool $allDay = false;

    protected bool $openEndTime = false;

    protected string $externalIcsUrl = '';

    /**
     * @var ObjectStorage<ConfigurationGroup>
     */
    #[Extbase\ORM\Lazy]
    protected ObjectStorage $groups;

    protected string $frequency = self::FREQUENCY_NONE;

    protected ?\DateTime $tillDate = null;

    protected ?int $tillDays = null;

    protected bool $tillDaysRelative = false;

    protected ?int $tillDaysPast = null;

    protected int $counterAmount = 0;

    protected int $counterInterval = 1;

    protected string $recurrence = self::RECURRENCE_NONE;

    protected string $day = self::DAY_NONE;

    protected bool $hidden = false;

    protected string $flexForm = '';

    /**
     * Configuration constructor.
     */
    public function __construct()
    {
        $this->groups = new ObjectStorage();
    }

    /**
     * Get type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set type.
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Is all day.
     */
    public function isAllDay(): bool
    {
        return $this->allDay;
    }

    /**
     * Set all day.
     */
    public function setAllDay(bool $allDay): self
    {
        $this->allDay = $allDay;

        return $this;
    }

    /**
     * Get end date.
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * Set end date.
     */
    public function setEndDate(?\DateTime $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get end date dynamic.
     */
    public function getEndDateDynamic(): string
    {
        return $this->endDateDynamic;
    }

    /**
     * Set end date dynamic.
     */
    public function setEndDateDynamic(string $endDateDynamic): self
    {
        $this->endDateDynamic = $endDateDynamic;

        return $this;
    }

    /**
     * Get end time.
     */
    public function getEndTime(): int
    {
        return $this->endTime;
    }

    /**
     * Set end time.
     */
    public function setEndTime(int $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get start date.
     */
    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    /**
     * Set start date.
     */
    public function setStartDate(?\DateTime $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get start time.
     */
    public function getStartTime(): int
    {
        return $this->startTime;
    }

    /**
     * Set start time.
     */
    public function setStartTime(int $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get groups.
     */
    public function getGroups(): ObjectStorage
    {
        return $this->groups ?? new ObjectStorage();
    }

    /**
     * Set groups.
     */
    public function setGroups(ObjectStorage $groups): self
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get frequency.
     */
    public function getFrequency(): string
    {
        return $this->frequency;
    }

    /**
     * Set frequency.
     */
    public function setFrequency(string $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Get till date.
     */
    public function getTillDate(): ?\DateTime
    {
        return $this->tillDate;
    }

    /**
     * Set till date.
     */
    public function setTillDate(?\DateTime $tillDate): self
    {
        $this->tillDate = $tillDate;

        return $this;
    }

    /**
     * Get till days.
     */
    public function getTillDays(): ?int
    {
        return $this->tillDays;
    }

    /**
     * Set till days.
     */
    public function setTillDays(?int $tillDays): self
    {
        $this->tillDays = $tillDays;

        return $this;
    }

    /**
     * Is till days relative.
     */
    public function isTillDaysRelative(): bool
    {
        return $this->tillDaysRelative;
    }

    /**
     * Set till days relative.
     */
    public function setTillDaysRelative(bool $tillDaysRelative): self
    {
        $this->tillDaysRelative = $tillDaysRelative;

        return $this;
    }

    /**
     * Get till days past.
     */
    public function getTillDaysPast(): ?int
    {
        return $this->tillDaysPast;
    }

    /**
     * Set till days past.
     */
    public function setTillDaysPast(?int $tillDaysPast): self
    {
        $this->tillDaysPast = $tillDaysPast;

        return $this;
    }

    /**
     * Get counter amount.
     */
    public function getCounterAmount(): int
    {
        return $this->counterAmount;
    }

    /**
     * Set counter amount.
     */
    public function setCounterAmount(int $counterAmount): self
    {
        $this->counterAmount = $counterAmount;

        return $this;
    }

    /**
     * Get counter interval.
     */
    public function getCounterInterval(): int
    {
        return $this->counterInterval;
    }

    /**
     * Set counter interval.
     */
    public function setCounterInterval(int $counterInterval): self
    {
        $this->counterInterval = $counterInterval;

        return $this;
    }

    /**
     * Get external ICS URL.
     */
    public function getExternalIcsUrl(): string
    {
        return $this->externalIcsUrl;
    }

    /**
     * Set external ICS URL.
     */
    public function setExternalIcsUrl(string $externalIcsUrl): self
    {
        $this->externalIcsUrl = $externalIcsUrl;

        return $this;
    }

    /**
     * Get day.
     */
    public function getDay(): string
    {
        return $this->day;
    }

    /**
     * Set day.
     */
    public function setDay(string $day): self
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get recurrence.
     */
    public function getRecurrence(): string
    {
        return $this->recurrence;
    }

    /**
     * Set recurrence.
     */
    public function setRecurrence(string $recurrence): self
    {
        $this->recurrence = $recurrence;

        return $this;
    }

    /**
     * Get handling.
     */
    public function getHandling(): string
    {
        return $this->handling;
    }

    /**
     * Set handling.
     */
    public function setHandling(string $handling): self
    {
        $this->handling = $handling;

        return $this;
    }

    /**
     * Get state.
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Set state.
     */
    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function isOpenEndTime(): bool
    {
        return $this->openEndTime;
    }

    public function setOpenEndTime(bool $openEndTime): self
    {
        $this->openEndTime = $openEndTime;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getFlexForm(): string
    {
        return $this->flexForm;
    }

    public function setFlexForm(string $flexForm): void
    {
        $this->flexForm = $flexForm;
    }
}
