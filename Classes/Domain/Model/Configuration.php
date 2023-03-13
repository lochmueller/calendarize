<?php

/**
 * Configuration for time options.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Configuration for time options.
 *
 * @DatabaseTable
 */
class Configuration extends AbstractModel implements ConfigurationInterface
{
    use ImportTrait;
    /**
     * Type.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $type = self::TYPE_TIME;

    /**
     * Handling.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $handling = self::HANDLING_INCLUDE;

    /**
     * State.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $state = self::STATE_DEFAULT;

    /**
     * Start date.
     *
     * @var \DateTime|null
     *
     * @DatabaseField(type="\DateTime", sql="date default NULL")
     */
    protected $startDate;

    /**
     * End date.
     *
     * @var \DateTime|null
     *
     * @DatabaseField(type="\DateTime", sql="date default NULL")
     */
    protected $endDate;

    /**
     * End date dynamic.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $endDateDynamic = '';

    /**
     * Start time.
     *
     * @var int
     *
     * @DatabaseField("int")
     */
    protected $startTime = 0;

    /**
     * End time.
     *
     * @var int
     *
     * @DatabaseField("int")
     */
    protected $endTime = 0;

    /**
     * AllDay.
     *
     * @var bool
     *
     * @DatabaseField("bool")
     */
    protected $allDay = false;

    /**
     * OpenEndTime.
     *
     * @var bool
     *
     * @DatabaseField("bool")
     */
    protected $openEndTime = false;

    /**
     * External ICS url.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $externalIcsUrl = '';

    /**
     * Groups.
     *
     * @var ObjectStorage<ConfigurationGroup>
     *
     * @DatabaseField("\TYPO3\CMS\Extbase\Persistence\ObjectStorage")
     *
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $groups;

    /**
     * Frequency.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $frequency = self::FREQUENCY_NONE;

    /**
     * Till date.
     *
     * @var \DateTime|null
     *
     * @DatabaseField(type="\DateTime", sql="date default NULL")
     */
    protected $tillDate;

    /**
     * Till days.
     *
     * @var int|null
     *
     * @DatabaseField(sql="int(11)")
     */
    protected $tillDays;

    /**
     * Till days relative.
     *
     * @var bool
     *
     * @DatabaseField("Boolean")
     */
    protected $tillDaysRelative = false;

    /**
     * Till days past.
     *
     * @var int|null
     *
     * @DatabaseField(sql="int(11)")
     */
    protected $tillDaysPast;

    /**
     * Counter amount.
     *
     * @var int
     *
     * @DatabaseField("int")
     */
    protected $counterAmount = 0;

    /**
     * Counter interval.
     *
     * @var int
     *
     * @DatabaseField("int")
     */
    protected $counterInterval = 1;

    /**
     * Recurrence.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $recurrence = self::RECURRENCE_NONE;

    /**
     * Day property.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $day = self::DAY_NONE;

    /**
     * Hidden.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * Flexform.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $flexForm = '';

    /**
     * Configuration constructor.
     */
    public function __construct()
    {
        $this->groups = new ObjectStorage();
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param string $type
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Is all day.
     *
     * @return bool
     */
    public function isAllDay(): bool
    {
        return $this->allDay;
    }

    /**
     * Set all day.
     *
     * @param bool $allDay
     */
    public function setAllDay(bool $allDay): self
    {
        $this->allDay = $allDay;

        return $this;
    }

    /**
     * Get end date.
     *
     * @return \DateTime|null
     */
    public function getEndDate(): ?\DateTime
    {
        return DateTimeUtility::fixDateTimeForExtbase($this->endDate);
    }

    /**
     * Set end date.
     *
     * @param \DateTime|null $endDate
     */
    public function setEndDate(?\DateTime $endDate): self
    {
        $this->endDate = DateTimeUtility::fixDateTimeForDb($endDate);

        return $this;
    }

    /**
     * Get end date dynamic.
     *
     * @return string
     */
    public function getEndDateDynamic(): string
    {
        return $this->endDateDynamic;
    }

    /**
     * Set end date dynamic.
     *
     * @param string $endDateDynamic
     */
    public function setEndDateDynamic(string $endDateDynamic): self
    {
        $this->endDateDynamic = $endDateDynamic;

        return $this;
    }

    /**
     * Get end time.
     *
     * @return int
     */
    public function getEndTime(): int
    {
        return $this->endTime;
    }

    /**
     * Set end time.
     *
     * @param int $endTime
     */
    public function setEndTime(int $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get start date.
     *
     * @return \DateTime|null
     */
    public function getStartDate(): ?\DateTime
    {
        return DateTimeUtility::fixDateTimeForExtbase($this->startDate);
    }

    /**
     * Set start date.
     *
     * @param \DateTime|null $startDate
     */
    public function setStartDate(?\DateTime $startDate): self
    {
        $this->startDate = DateTimeUtility::fixDateTimeForDb($startDate);

        return $this;
    }

    /**
     * Get start time.
     *
     * @return int
     */
    public function getStartTime(): int
    {
        return $this->startTime;
    }

    /**
     * Set start time.
     *
     * @param int $startTime
     */
    public function setStartTime(int $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get groups.
     *
     * @return ObjectStorage<ConfigurationGroup>
     */
    public function getGroups(): ObjectStorage
    {
        return $this->groups ?? new ObjectStorage();
    }

    /**
     * Set groups.
     *
     * @param ObjectStorage $groups
     */
    public function setGroups(ObjectStorage $groups): self
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get frequency.
     *
     * @return string
     */
    public function getFrequency(): string
    {
        return $this->frequency;
    }

    /**
     * Set frequency.
     *
     * @param string $frequency
     */
    public function setFrequency(string $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Get till date.
     *
     * @return \DateTime|null
     */
    public function getTillDate(): ?\DateTime
    {
        return DateTimeUtility::fixDateTimeForExtbase($this->tillDate);
    }

    /**
     * Set till date.
     *
     * @param \DateTime|null $tillDate
     */
    public function setTillDate(?\DateTime $tillDate): self
    {
        $this->tillDate = DateTimeUtility::fixDateTimeForDb($tillDate);

        return $this;
    }

    /**
     * Get till days.
     *
     * @return int|null
     */
    public function getTillDays(): ?int
    {
        return $this->tillDays;
    }

    /**
     * Set till days.
     *
     * @param int|null $tillDays
     */
    public function setTillDays(?int $tillDays): self
    {
        $this->tillDays = $tillDays;

        return $this;
    }

    /**
     * Is till days relative.
     *
     * @return bool
     */
    public function isTillDaysRelative(): bool
    {
        return $this->tillDaysRelative;
    }

    /**
     * Set till days relative.
     *
     * @param bool $tillDaysRelative
     */
    public function setTillDaysRelative(bool $tillDaysRelative): self
    {
        $this->tillDaysRelative = $tillDaysRelative;

        return $this;
    }

    /**
     * Get till days past.
     *
     * @return int|null
     */
    public function getTillDaysPast(): ?int
    {
        return $this->tillDaysPast;
    }

    /**
     * Set till days past.
     *
     * @param int|null $tillDaysPast
     */
    public function setTillDaysPast(?int $tillDaysPast): self
    {
        $this->tillDaysPast = $tillDaysPast;

        return $this;
    }

    /**
     * Get counter amount.
     *
     * @return int
     */
    public function getCounterAmount(): int
    {
        return $this->counterAmount;
    }

    /**
     * Set counter amount.
     *
     * @param int $counterAmount
     */
    public function setCounterAmount(int $counterAmount): self
    {
        $this->counterAmount = $counterAmount;

        return $this;
    }

    /**
     * Get counter interval.
     *
     * @return int
     */
    public function getCounterInterval(): int
    {
        return $this->counterInterval;
    }

    /**
     * Set counter interval.
     *
     * @param int $counterInterval
     */
    public function setCounterInterval(int $counterInterval): self
    {
        $this->counterInterval = $counterInterval;

        return $this;
    }

    /**
     * Get external ICS URL.
     *
     * @return string
     */
    public function getExternalIcsUrl(): string
    {
        return $this->externalIcsUrl;
    }

    /**
     * Set external ICS URL.
     *
     * @param string $externalIcsUrl
     */
    public function setExternalIcsUrl(string $externalIcsUrl): self
    {
        $this->externalIcsUrl = $externalIcsUrl;

        return $this;
    }

    /**
     * Get day.
     *
     * @return string
     */
    public function getDay(): string
    {
        return $this->day;
    }

    /**
     * Set day.
     *
     * @param string $day
     */
    public function setDay(string $day): self
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get recurrence.
     *
     * @return string
     */
    public function getRecurrence(): string
    {
        return $this->recurrence;
    }

    /**
     * Set recurrence.
     *
     * @param string $recurrence
     */
    public function setRecurrence(string $recurrence): self
    {
        $this->recurrence = $recurrence;

        return $this;
    }

    /**
     * Get handling.
     *
     * @return string
     */
    public function getHandling(): string
    {
        return $this->handling;
    }

    /**
     * Set handling.
     *
     * @param string $handling
     */
    public function setHandling(string $handling): self
    {
        $this->handling = $handling;

        return $this;
    }

    /**
     * Get state.
     *
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Set state.
     *
     * @param string $state
     */
    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOpenEndTime(): bool
    {
        return $this->openEndTime;
    }

    /**
     * @param bool $openEndTime
     */
    public function setOpenEndTime(bool $openEndTime): self
    {
        $this->openEndTime = $openEndTime;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlexForm(): string
    {
        return $this->flexForm;
    }

    /**
     * @param string $flexForm
     */
    public function setFlexForm(string $flexForm): void
    {
        $this->flexForm = $flexForm;
    }
}
