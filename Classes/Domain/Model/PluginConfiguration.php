<?php

/**
 * PluginConfiguration.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\SmartExclude;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * PluginConfiguration.
 *
 * @DatabaseTable
 * @SmartExclude(excludes={"Workspaces"})
 */
class PluginConfiguration extends AbstractModel
{
    /**
     * Title.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $title;

    /**
     * Model name.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $modelName;

    /**
     * Configuration / Element Type / Record Type.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $configuration;

    /**
     * Storage PID.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $storagePid;

    /**
     * Recursive.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $recursive;

    /**
     * Detail PID.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $detailPid;

    /**
     * List PID.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $listPid;

    /**
     * Year PID.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $yearPid;

    /**
     * Month PID.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $monthPid;

    /**
     * Week PID.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $weekPid;

    /**
     * Day PID.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $dayPid;

    /**
     * Booking PID.
     *
     * @var int
     * @DatabaseField("int")
     */
    protected $bookingPid;

    /**
     * Categories.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    protected $categories;

    /**
     * Build up the plugin configuration.
     */
    public function __construct()
    {
        $this->categories = new ObjectStorage();
    }

    /**
     * Get title.
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get configuration.
     *
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Set configuration.
     *
     * @param string $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get storage PID.
     *
     * @return string
     */
    public function getStoragePid()
    {
        return $this->storagePid;
    }

    /**
     * Set storage PID.
     *
     * @param string $storagePid
     */
    public function setStoragePid($storagePid)
    {
        $this->storagePid = $storagePid;
    }

    /**
     * Get recursive.
     *
     * @return int
     */
    public function getRecursive()
    {
        return $this->recursive;
    }

    /**
     * Set recursive.
     *
     * @param int $recursive
     */
    public function setRecursive($recursive)
    {
        $this->recursive = $recursive;
    }

    /**
     * Get detail PID.
     *
     * @return int
     */
    public function getDetailPid()
    {
        return $this->detailPid;
    }

    /**
     * Set detail PID.
     *
     * @param int $detailPid
     */
    public function setDetailPid($detailPid)
    {
        $this->detailPid = $detailPid;
    }

    /**
     * Get list PID.
     *
     * @return int
     */
    public function getListPid()
    {
        return $this->listPid;
    }

    /**
     * Set list PID.
     *
     * @param int $listPid
     */
    public function setListPid($listPid)
    {
        $this->listPid = $listPid;
    }

    /**
     * Get year PID.
     *
     * @return int
     */
    public function getYearPid()
    {
        return $this->yearPid;
    }

    /**
     * Set year PID.
     *
     * @param int $yearPid
     */
    public function setYearPid($yearPid)
    {
        $this->yearPid = $yearPid;
    }

    /**
     * Get month PID.
     *
     * @return int
     */
    public function getMonthPid()
    {
        return $this->monthPid;
    }

    /**
     * Set month PID.
     *
     * @param int $monthPid
     */
    public function setMonthPid($monthPid)
    {
        $this->monthPid = $monthPid;
    }

    /**
     * Get week PID.
     *
     * @return int
     */
    public function getWeekPid()
    {
        return $this->weekPid;
    }

    /**
     * Set week PID.
     *
     * @param int $weekPid
     */
    public function setWeekPid($weekPid)
    {
        $this->weekPid = $weekPid;
    }

    /**
     * Get day PID.
     *
     * @return int
     */
    public function getDayPid()
    {
        return $this->dayPid;
    }

    /**
     * Set day PID.
     *
     * @param int $dayPid
     */
    public function setDayPid($dayPid)
    {
        $this->dayPid = $dayPid;
    }

    /**
     * Get booking PID.
     *
     * @return int
     */
    public function getBookingPid()
    {
        return $this->bookingPid;
    }

    /**
     * Set booking PID.
     *
     * @param int $bookingPid
     */
    public function setBookingPid($bookingPid)
    {
        $this->bookingPid = $bookingPid;
    }

    /**
     * Get method name.
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * Set method name.
     *
     * @param string $modelName
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    /**
     * Get categories.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getCategories(): ObjectStorage
    {
        if (!($this->categories instanceof ObjectStorage)) {
            return new ObjectStorage();
        }

        return $this->categories;
    }

    /**
     * Set categories.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function setCategories(ObjectStorage $categories)
    {
        $this->categories = $categories;
    }
}
