<?php

/**
 * Index information.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\SmartExclude;
use HDNET\Calendarize\Exception;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\EventUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Index information.
 *
 * @DatabaseTable
 * @SmartExclude("Workspaces")
 */
class Index extends AbstractModel
{
    /**
     * The unique register key of the used table/model configuration.
     *
     * @var string
     * @DatabaseField(sql="varchar(150) DEFAULT '' NOT NULL")
     */
    protected $uniqueRegisterKey;

    /**
     * TableName.
     *
     * @var string
     * @DatabaseField(sql="varchar(150) DEFAULT '' NOT NULL")
     */
    protected $foreignTable;

    /**
     * The Id of the foreign element.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $foreignUid;

    /**
     * Start date.
     *
     * @var \DateTime
     * @DatabaseField(sql="date default NULL")
     */
    protected $startDate;

    /**
     * End date.
     *
     * @var \DateTime
     * @DatabaseField(sql="date default NULL")
     */
    protected $endDate;

    /**
     * Start time.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $startTime;

    /**
     * End time.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $endTime;

    /**
     * AllDay.
     *
     * @var bool
     * @DatabaseField("bool")
     */
    protected $allDay;

    /**
     * OpenEndTime.
     *
     * @var bool
     * @DatabaseField("bool")
     */
    protected $openEndTime;

    /**
     * State.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $state;

    /**
     * The original object.
     *
     * @var AbstractEntity
     */
    protected $originalObject;

    /**
     * Get the original record for the current index.
     *
     * @throws Exception
     *
     * @return AbstractEntity
     */
    public function getOriginalObject()
    {
        if (null === $this->originalObject) {
            $configuration = $this->getConfiguration();
            if (empty($configuration)) {
                throw new Exception('No valid configuration for the current index: ' . $this->getUniqueRegisterKey(), 123678123);
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
     * @return \DateTime
     */
    public function getStartDateComplete()
    {
        $date = $this->getStartDate();
        if (!$this->isAllDay() && $date instanceof \DateTimeInterface) {
            $time = DateTimeUtility::normalizeDateTimeSingle($this->getStartTime());

            return \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $time->format('H:i'));
        }

        return $date;
    }

    /**
     * Get the complete end date.
     *
     * @return \DateTime
     */
    public function getEndDateComplete()
    {
        $date = $this->getEndDate();
        if (!$this->isAllDay() && $date instanceof \DateTimeInterface) {
            $time = DateTimeUtility::normalizeDateTimeSingle($this->getEndTime());

            return \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $time->format('H:i'));
        }

        return $date;
    }

    /**
     * Set foreign uid.
     *
     * @param int $foreignUid
     */
    public function setForeignUid($foreignUid)
    {
        $this->foreignUid = $foreignUid;
    }

    /**
     * Get foreign uid.
     *
     * @return int
     */
    public function getForeignUid()
    {
        return $this->foreignUid;
    }

    /**
     * Set unique register key.
     *
     * @param string $uniqueRegisterKey
     */
    public function setUniqueRegisterKey($uniqueRegisterKey)
    {
        $this->uniqueRegisterKey = $uniqueRegisterKey;
    }

    /**
     * Get unique register key.
     *
     * @return string
     */
    public function getUniqueRegisterKey()
    {
        return $this->uniqueRegisterKey;
    }

    /**
     * Set foreign table.
     *
     * @param string $foreignTable
     */
    public function setForeignTable($foreignTable)
    {
        $this->foreignTable = $foreignTable;
    }

    /**
     * Get foreign table.
     *
     * @return string
     */
    public function getForeignTable()
    {
        return $this->foreignTable;
    }

    /**
     * Set all day.
     *
     * @param bool $allDay
     */
    public function setAllDay($allDay)
    {
        $this->allDay = $allDay;
    }

    /**
     * Is all day.
     *
     * @return bool
     */
    public function isAllDay()
    {
        return (bool)$this->allDay;
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
     * Get end date.
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
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
     * Get end time.
     *
     * @return int
     */
    public function getEndTime()
    {
        return $this->endTime;
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
     * Get start date.
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
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
     * Get start time.
     *
     * @return int
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Get state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
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

    /**
     * Get sys language uid.
     *
     * @return int
     */
    public function getSysLanguageUid()
    {
        return (int)$this->_languageUid;
    }

    /**
     * @return bool
     */
    public function isOpenEndTime()
    {
        return $this->openEndTime;
    }

    /**
     * @param bool $openEndTime
     */
    public function setOpenEndTime($openEndTime)
    {
        $this->openEndTime = $openEndTime;
    }
}
