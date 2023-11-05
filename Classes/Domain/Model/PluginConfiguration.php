<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * PluginConfiguration.
 */
class PluginConfiguration extends AbstractModel
{
    protected string $title = '';

    protected string $modelName = '';

    /**
     * Configuration / Element Type / Record Type.
     */
    protected string $configuration = '';

    protected string $storagePid;

    protected int $recursive;

    protected int $detailPid;

    protected int $listPid;

    protected int $yearPid;

    protected int $quarterPid;

    protected int $monthPid;

    protected int $weekPid;

    protected int $dayPid;

    protected int $bookingPid;

    /**
     * Categories.
     *
     * @var ObjectStorage<Category>
     */
    protected ObjectStorage $categories;

    /**
     * Build up the plugin configuration.
     */
    public function __construct()
    {
        $this->categories = new ObjectStorage();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function setModelName(string $modelName): void
    {
        $this->modelName = $modelName;
    }

    public function getConfiguration(): string
    {
        return $this->configuration;
    }

    public function setConfiguration(string $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getStoragePid(): string
    {
        return $this->storagePid;
    }

    public function setStoragePid(string $storagePid): void
    {
        $this->storagePid = $storagePid;
    }

    public function getRecursive(): int
    {
        return $this->recursive;
    }

    public function setRecursive(int $recursive): void
    {
        $this->recursive = $recursive;
    }

    public function getDetailPid(): int
    {
        return $this->detailPid;
    }

    public function setDetailPid(int $detailPid): void
    {
        $this->detailPid = $detailPid;
    }

    public function getListPid(): int
    {
        return $this->listPid;
    }

    public function setListPid(int $listPid): void
    {
        $this->listPid = $listPid;
    }

    public function getYearPid(): int
    {
        return $this->yearPid;
    }

    public function setYearPid(int $yearPid): void
    {
        $this->yearPid = $yearPid;
    }

    public function getQuarterPid(): int
    {
        return $this->quarterPid;
    }

    public function setQuarterPid(int $quarterPid): void
    {
        $this->quarterPid = $quarterPid;
    }

    public function getMonthPid(): int
    {
        return $this->monthPid;
    }

    public function setMonthPid(int $monthPid): void
    {
        $this->monthPid = $monthPid;
    }

    public function getWeekPid(): int
    {
        return $this->weekPid;
    }

    public function setWeekPid(int $weekPid): void
    {
        $this->weekPid = $weekPid;
    }

    public function getDayPid(): int
    {
        return $this->dayPid;
    }

    public function setDayPid(int $dayPid): void
    {
        $this->dayPid = $dayPid;
    }

    public function getBookingPid(): int
    {
        return $this->bookingPid;
    }

    public function setBookingPid(int $bookingPid): void
    {
        $this->bookingPid = $bookingPid;
    }

    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }
}
