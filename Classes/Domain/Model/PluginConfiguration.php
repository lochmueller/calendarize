<?php

/**
 * PluginConfiguration
 */

namespace HDNET\Calendarize\Domain\Model;

/**
 * PluginConfiguration
 *
 * @db
 */
class PluginConfiguration extends AbstractModel
{

    /**
     * Title
     *
     * @var string
     * @db
     */
    protected $title;

    /**
     * @var string
     * @db
     */
    protected $modelName;

    /**
     * Configuration / Element Type / Record Type
     *
     * @var string
     * @db
     */
    protected $configuration;

    /**
     * @var string
     * @db
     */
    protected $storagePid;
    /**
     * @var int
     * @db
     */
    protected $recursive;
    /**
     * @var int
     * @db
     */
    protected $detailPid;
    /**
     * @var int
     * @db
     */
    protected $listPid;
    /**
     * @var int
     * @db
     */
    protected $yearPid;
    /**
     * @var int
     * @db
     */
    protected $monthPid;
    /**
     * @var int
     * @db
     */
    protected $weekPid;
    /**
     * @var int
     * @db
     */
    protected $dayPid;
    /**
     * @var int
     * @db
     */
    protected $bookingPid;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getConfiguration(): string
    {
        return $this->configuration;
    }

    /**
     * @param string $configuration
     */
    public function setConfiguration(string $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getStoragePid(): string
    {
        return $this->storagePid;
    }

    /**
     * @param string $storagePid
     */
    public function setStoragePid(string $storagePid)
    {
        $this->storagePid = $storagePid;
    }

    /**
     * @return int
     */
    public function getRecursive(): int
    {
        return $this->recursive;
    }

    /**
     * @param int $recursive
     */
    public function setRecursive(int $recursive)
    {
        $this->recursive = $recursive;
    }

    /**
     * @return int
     */
    public function getDetailPid(): int
    {
        return $this->detailPid;
    }

    /**
     * @param int $detailPid
     */
    public function setDetailPid(int $detailPid)
    {
        $this->detailPid = $detailPid;
    }

    /**
     * @return int
     */
    public function getListPid(): int
    {
        return $this->listPid;
    }

    /**
     * @param int $listPid
     */
    public function setListPid(int $listPid)
    {
        $this->listPid = $listPid;
    }

    /**
     * @return int
     */
    public function getYearPid(): int
    {
        return $this->yearPid;
    }

    /**
     * @param int $yearPid
     */
    public function setYearPid(int $yearPid)
    {
        $this->yearPid = $yearPid;
    }

    /**
     * @return int
     */
    public function getMonthPid(): int
    {
        return $this->monthPid;
    }

    /**
     * @param int $monthPid
     */
    public function setMonthPid(int $monthPid)
    {
        $this->monthPid = $monthPid;
    }

    /**
     * @return int
     */
    public function getWeekPid(): int
    {
        return $this->weekPid;
    }

    /**
     * @param int $weekPid
     */
    public function setWeekPid(int $weekPid)
    {
        $this->weekPid = $weekPid;
    }

    /**
     * @return int
     */
    public function getDayPid(): int
    {
        return $this->dayPid;
    }

    /**
     * @param int $dayPid
     */
    public function setDayPid(int $dayPid)
    {
        $this->dayPid = $dayPid;
    }

    /**
     * @return int
     */
    public function getBookingPid(): int
    {
        return $this->bookingPid;
    }

    /**
     * @param int $bookingPid
     */
    public function setBookingPid(int $bookingPid)
    {
        $this->bookingPid = $bookingPid;
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return $this->modelName;
    }

    /**
     * @param string $modelName
     */
    public function setModelName(string $modelName)
    {
        $this->modelName = $modelName;
    }
}
