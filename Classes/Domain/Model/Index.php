<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Calendarize\Exception\InvalidConfigurationException;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\EventUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Index information.
 */
class Index extends AbstractModel
{
    protected \DateTime $tstamp;

    /**
     * The unique register key of the used table/model configuration.
     */
    protected string $uniqueRegisterKey = '';

    protected string $foreignTable = '';

    /**
     * The uid of the foreign element.
     */
    protected int $foreignUid = 0;

    protected ?\DateTime $startDate = null;

    protected ?\DateTime $endDate = null;

    protected int $startTime = 0;

    protected int $endTime = 0;

    protected bool $allDay = false;

    protected bool $openEndTime = false;

    protected string $state = '';

    /**
     * The original object.
     */
    protected ?AbstractEntity $originalObject = null;

    protected string $slug = '';

    /**
     * Get the original record for the current index.
     *
     * @throws InvalidConfigurationException
     */
    public function getOriginalObject(): ?AbstractEntity
    {
        if (null === $this->originalObject) {
            $configuration = $this->getConfiguration();
            if (empty($configuration)) {
                throw new InvalidConfigurationException('No valid configuration for the current index: ' . $this->getUniqueRegisterKey(), 123678123);
            }
            $this->originalObject = EventUtility::getOriginalRecordByConfiguration(
                $configuration,
                $this->getForeignUid(),
            );
        }

        return $this->originalObject;
    }

    /**
     * Get the current configuration.
     */
    public function getConfiguration(): array
    {
        return Register::getRegister()[$this->getUniqueRegisterKey()] ?? [];
    }

    /**
     * Get the complete start date.
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

    public function getTstamp(): \DateTime
    {
        return $this->tstamp;
    }

    /**
     * Set foreign uid.
     */
    public function setForeignUid(int $foreignUid): void
    {
        $this->foreignUid = $foreignUid;
    }

    /**
     * Get foreign uid.
     */
    public function getForeignUid(): int
    {
        return $this->foreignUid;
    }

    /**
     * Set unique register key.
     */
    public function setUniqueRegisterKey(string $uniqueRegisterKey): void
    {
        $this->uniqueRegisterKey = $uniqueRegisterKey;
    }

    /**
     * Get unique register key.
     */
    public function getUniqueRegisterKey(): string
    {
        return $this->uniqueRegisterKey;
    }

    /**
     * Set foreign table.
     */
    public function setForeignTable(string $foreignTable): void
    {
        $this->foreignTable = $foreignTable;
    }

    /**
     * Get foreign table.
     */
    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }

    /**
     * Set all day.
     */
    public function setAllDay(bool $allDay): void
    {
        $this->allDay = $allDay;
    }

    /**
     * Is all day.
     */
    public function isAllDay(): bool
    {
        return $this->allDay;
    }

    /**
     * Set end date.
     */
    public function setEndDate(?\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * Get end date.
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * Set end time.
     */
    public function setEndTime(int $endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * Get end time.
     */
    public function getEndTime(): int
    {
        return $this->endTime;
    }

    /**
     * Set start date.
     */
    public function setStartDate(?\DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * Get start date.
     */
    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    /**
     * Set start time.
     */
    public function setStartTime(int $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * Get start time.
     */
    public function getStartTime(): int
    {
        return $this->startTime;
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
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * Get sys language uid.
     */
    public function getSysLanguageUid(): int
    {
        return (int)$this->_languageUid;
    }

    public function isOpenEndTime(): bool
    {
        return $this->openEndTime;
    }

    public function setOpenEndTime(bool $openEndTime): void
    {
        $this->openEndTime = $openEndTime;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
