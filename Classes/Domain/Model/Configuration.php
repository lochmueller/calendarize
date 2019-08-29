<?php

/**
 * Configuration for time options.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Calendarize\Utility\DateTimeUtility;

/**
 * Configuration for time options.
 *
 * @db
 * @smartExclude Language
 */
class Configuration extends AbstractModel implements ConfigurationInterface
{
    /**
     * Type.
     *
     * @var string
     * @db
     */
    protected $type = self::TYPE_TIME;

    /**
     * Handling.
     *
     * @var string
     * @db
     */
    protected $handling = self::HANDLING_INCLUDE;

    /**
     * State.
     *
     * @var string
     * @db
     */
    protected $state = self::STATE_DEFAULT;

    /**
     * Start date.
     *
     * @var \DateTime
     * @db
     */
    protected $startDate;

    /**
     * End date.
     *
     * @var \DateTime
     * @db
     */
    protected $endDate;

    /**
     * End date dynamic.
     *
     * @var string
     * @db
     */
    protected $endDateDynamic;

    /**
     * Start time.
     *
     * @var int
     * @db
     */
    protected $startTime;

    /**
     * End time.
     *
     * @var int
     * @db
     */
    protected $endTime;

    /**
     * AllDay.
     *
     * @var bool
     * @db
     */
    protected $allDay;

    /**
     * External ICS url.
     *
     * @var string
     * @db
     */
    protected $externalIcsUrl;

    /**
     * Groups.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HDNET\Calendarize\Domain\Model\ConfigurationGroup>
     * @db text
     * @lazy
     */
    protected $groups;

    /**
     * Frequency.
     *
     * @var string
     * @db
     */
    protected $frequency = self::FREQUENCY_NONE;

    /**
     * Till date.
     *
     * @var \DateTime
     * @db
     */
    protected $tillDate;

    /**
     * Counter amount.
     *
     * @var int
     * @db
     */
    protected $counterAmount;

    /**
     * Counter interval.
     *
     * @var int
     * @db
     */
    protected $counterInterval;

    /**
     * Recurrence.
     *
     * @var string
     * @db
     */
    protected $recurrence = self::RECURRENCE_NONE;

    /**
     * Day property.
     *
     * @var string
     * @db
     */
    protected $day = self::DAY_NONE;

    /**
     * Import ID if the item is based on an ICS structure.
     *
     * @var string
     * @db
     */
    protected $importId;

    /**
     * Configuration constructor.
     */
    public function __construct()
    {
        $this->groups = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Is all day.
     *
     * @return bool
     */
    public function isAllDay()
    {
        return (bool) $this->allDay;
    }

    /**
     * Set all day.
     *
     * @param bool $allDay
     */
    public function setAllDay($allDay)
    {
        $this->allDay = (bool) $allDay;
    }

    /**
     * Get end date.
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        if ($this->endDate instanceof \DateTime) {
            $this->endDate->setTimezone(DateTimeUtility::getTimeZone());
        }

        return $this->endDate;
    }

    /**
     * Set end date.
     *
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get end date dynamic.
     *
     * @return string
     */
    public function getEndDateDynamic()
    {
        return $this->endDateDynamic;
    }

    /**
     * Set end date dynamic.
     *
     * @param string $endDateDynamic
     */
    public function setEndDateDynamic($endDateDynamic)
    {
        $this->endDateDynamic = $endDateDynamic;
    }

    /**
     * Get end time.
     *
     * @return int
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set end time.
     *
     * @param int $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * Get start date.
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        if ($this->startDate instanceof \DateTime) {
            $this->startDate->setTimezone(DateTimeUtility::getTimeZone());
        }

        return $this->startDate;
    }

    /**
     * Set start date.
     *
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Get start time.
     *
     * @return int
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set start time.
     *
     * @param int $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * Get groups.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getGroups()
    {
        if (null === $this->groups) {
            return new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        }

        return $this->groups;
    }

    /**
     * Set groups.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * Get frequency.
     *
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set frequency.
     *
     * @param string $frequency
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }

    /**
     * Get till date.
     *
     * @return \DateTime
     */
    public function getTillDate()
    {
        return $this->tillDate;
    }

    /**
     * Set till date.
     *
     * @param \DateTime $tillDate
     */
    public function setTillDate($tillDate)
    {
        $this->tillDate = $tillDate;
    }

    /**
     * Get counter amount.
     *
     * @return int
     */
    public function getCounterAmount()
    {
        return $this->counterAmount;
    }

    /**
     * Set counter amount.
     *
     * @param int $counterAmount
     */
    public function setCounterAmount($counterAmount)
    {
        $this->counterAmount = $counterAmount;
    }

    /**
     * Get counter interval.
     *
     * @return int
     */
    public function getCounterInterval()
    {
        return $this->counterInterval;
    }

    /**
     * Set counter interval.
     *
     * @param int $counterInterval
     */
    public function setCounterInterval($counterInterval)
    {
        $this->counterInterval = $counterInterval;
    }

    /**
     * Get external ICS URL.
     *
     * @return string
     */
    public function getExternalIcsUrl()
    {
        return $this->externalIcsUrl;
    }

    /**
     * Set external ICS URL.
     *
     * @param string $externalIcsUrl
     */
    public function setExternalIcsUrl($externalIcsUrl)
    {
        $this->externalIcsUrl = $externalIcsUrl;
    }

    /**
     * Get day.
     *
     * @return string
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set day.
     *
     * @param string $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * Get recurrence.
     *
     * @return string
     */
    public function getRecurrence()
    {
        return $this->recurrence;
    }

    /**
     * Set recurrence.
     *
     * @param string $recurrence
     */
    public function setRecurrence($recurrence)
    {
        $this->recurrence = $recurrence;
    }

    /**
     * Get handling.
     *
     * @return string
     */
    public function getHandling()
    {
        return $this->handling;
    }

    /**
     * Set handling.
     *
     * @param string $handling
     */
    public function setHandling($handling)
    {
        $this->handling = $handling;
    }

    /**
     * Get state.
     *
     * @return string
     */
    public function getState()
    {
        return \trim($this->state);
    }

    /**
     * Set state.
     *
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }
}
