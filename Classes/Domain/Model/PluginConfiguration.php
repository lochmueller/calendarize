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
     * Categories
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    protected $categories;

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
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param string $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getStoragePid()
    {
        return $this->storagePid;
    }

    /**
     * @param string $storagePid
     */
    public function setStoragePid($storagePid)
    {
        $this->storagePid = $storagePid;
    }

    /**
     * @return int
     */
    public function getRecursive()
    {
        return $this->recursive;
    }

    /**
     * @param int $recursive
     */
    public function setRecursive($recursive)
    {
        $this->recursive = $recursive;
    }

    /**
     * @return int
     */
    public function getDetailPid()
    {
        return $this->detailPid;
    }

    /**
     * @param int $detailPid
     */
    public function setDetailPid($detailPid)
    {
        $this->detailPid = $detailPid;
    }

    /**
     * @return int
     */
    public function getListPid()
    {
        return $this->listPid;
    }

    /**
     * @param int $listPid
     */
    public function setListPid($listPid)
    {
        $this->listPid = $listPid;
    }

    /**
     * @return int
     */
    public function getYearPid()
    {
        return $this->yearPid;
    }

    /**
     * @param int $yearPid
     */
    public function setYearPid($yearPid)
    {
        $this->yearPid = $yearPid;
    }

    /**
     * @return int
     */
    public function getMonthPid()
    {
        return $this->monthPid;
    }

    /**
     * @param int $monthPid
     */
    public function setMonthPid($monthPid)
    {
        $this->monthPid = $monthPid;
    }

    /**
     * @return int
     */
    public function getWeekPid()
    {
        return $this->weekPid;
    }

    /**
     * @param int $weekPid
     */
    public function setWeekPid($weekPid)
    {
        $this->weekPid = $weekPid;
    }

    /**
     * @return int
     */
    public function getDayPid()
    {
        return $this->dayPid;
    }

    /**
     * @param int $dayPid
     */
    public function setDayPid($dayPid)
    {
        $this->dayPid = $dayPid;
    }

    /**
     * @return int
     */
    public function getBookingPid()
    {
        return $this->bookingPid;
    }

    /**
     * @param int $bookingPid
     */
    public function setBookingPid($bookingPid)
    {
        $this->bookingPid = $bookingPid;
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @param string $modelName
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function setCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories)
    {
        $this->categories = $categories;
    }
}
