<?php

/**
 * Index information.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Calendarize\Exception\InvalidConfigurationException;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\EventUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Index information.
 *
 * @DatabaseTable
 */
class Index extends AbstractModel
{
    /**
     * The unique register key of the used table/model configuration.
     *
     * @var string
     * @DatabaseField(sql="varchar(150) DEFAULT '' NOT NULL")
     */
    protected $uniqueRegisterKey = '';

    /**
     * TableName.
     *
     * @var string
     * @DatabaseField(sql="varchar(150) DEFAULT '' NOT NULL")
     */
    protected $foreignTable = '';

    /**
     * The Id of the foreign element.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $foreignUid = 0;

    /**
     * Start date.
     *
     * @var \DateTime|null
     * @DatabaseField(sql="date default NULL")
     */
    protected $startDate;

    /**
     * End date.
     *
     * @var \DateTime|null
     * @DatabaseField(sql="date default NULL")
     */
    protected $endDate;

    /**
     * Start time.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $startTime = 0;

    /**
     * End time.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $endTime = 0;

    /**
     * AllDay.
     *
     * @var bool
     * @DatabaseField("bool")
     */
    protected $allDay = false;

    /**
     * OpenEndTime.
     *
     * @var bool
     * @DatabaseField("bool")
     */
    protected $openEndTime = false;

    /**
     * State.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $state = '';

    /**
     * The original object.
     *
     * @var AbstractEntity
     */
    protected $originalObject;

    /**
     * Slug.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $slug = '';

    /**
     * Get the original record for the current index.
     *
     * @return AbstractEntity
     *
     * @throws InvalidConfigurationException
     */
    public function getOriginalObject()
    {
        if (null === $this->originalObject) {
            $configuration = $this->getConfiguration();
            if (empty($configuration)) {
                throw new InvalidConfigurationException('No valid configuration for the current index: ' . $this->getUniqueRegisterKey(), 123678123);
            }
            $this->originalObject = EventUtility::getOriginalRecordByConfiguration($configuration, (int)$this->getForeignUid());
        }

        return $this->originalObject;
    }

    /**
     * Get the current configuration.
     *
     * @return array
     */
    public function getConfiguration(): array
    {
        foreach (Register::getRegister() as $key => $configuration) {
            if ($this->getUniqueRegisterKey() === $key) {
                return $configuration;
            }
        }

        return [];
    }

    /**
     * Get the complete start date.
     *
     * @return \DateTime|null
     */
    public function getStartDateComplete(): ?\DateTime
    {
        $date = $this->getStartDate();
        if (!$this->isAllDay() && $date instanceof \DateTimeInterface) {
            return DateTimeUtility::setSecondsOfDateTime($date, $this->getStartTime());
        }

        return $date;
    }

    /**
     * Get the complete end date.
     *
     * @return \DateTime|null
     */
    public function getEndDateComplete(): ?\DateTime
    {
        $date = $this->getEndDate();
        if (!$this->isAllDay() && $date instanceof \DateTimeInterface) {
            return DateTimeUtility::setSecondsOfDateTime($date, $this->getEndTime());
        }
        if ($this->isAllDay() && $date instanceof \DateTimeInterface) {
            return DateTimeUtility::getDayEnd($date);
        }

        return $date;
    }

    /**
     * Set foreign uid.
     *
     * @param int $foreignUid
     */
    public function setForeignUid(int $foreignUid)
    {
        $this->foreignUid = $foreignUid;
    }

    /**
     * Get foreign uid.
     *
     * @return int
     */
    public function getForeignUid(): int
    {
        return $this->foreignUid;
    }

    /**
     * Set unique register key.
     *
     * @param string $uniqueRegisterKey
     */
    public function setUniqueRegisterKey(string $uniqueRegisterKey)
    {
        $this->uniqueRegisterKey = $uniqueRegisterKey;
    }

    /**
     * Get unique register key.
     *
     * @return string
     */
    public function getUniqueRegisterKey(): string
    {
        return $this->uniqueRegisterKey;
    }

    /**
     * Set foreign table.
     *
     * @param string $foreignTable
     */
    public function setForeignTable(string $foreignTable)
    {
        $this->foreignTable = $foreignTable;
    }

    /**
     * Get foreign table.
     *
     * @return string
     */
    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }

    /**
     * Set all day.
     *
     * @param bool $allDay
     */
    public function setAllDay(bool $allDay)
    {
        $this->allDay = $allDay;
    }

    /**
     * Is all day.
     *
     * @return bool
     */
    public function isAllDay(): bool
    {
        return (bool)$this->allDay;
    }

    /**
     * Set end date.
     *
     * @param \DateTime|null $endDate
     */
    public function setEndDate(?\DateTime $endDate): void
    {
        $this->endDate = DateTimeUtility::fixDateTimeForDb($endDate);
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
     * Set end time.
     *
     * @param int $endTime
     */
    public function setEndTime(int $endTime)
    {
        $this->endTime = $endTime;
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
     * Set start date.
     *
     * @param \DateTime|null $startDate
     */
    public function setStartDate(?\DateTime $startDate): void
    {
        $this->startDate = DateTimeUtility::fixDateTimeForDb($startDate);
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
     * Set start time.
     *
     * @param int $startTime
     */
    public function setStartTime(int $startTime)
    {
        $this->startTime = $startTime;
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
    public function setState(string $state)
    {
        $this->state = $state;
    }

    /**
     * Get sys language uid.
     *
     * @return int
     */
    public function getSysLanguageUid(): int
    {
        return (int)$this->_languageUid;
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
    public function setOpenEndTime(bool $openEndTime)
    {
        $this->openEndTime = $openEndTime;
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
