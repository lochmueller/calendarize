<?php

/**
 * Logical configuration group.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Logical configuration group.
 *
 * @db
 * @smartExclude Language
 */
class ConfigurationGroup extends AbstractModel
{
    /**
     * Title.
     *
     * @var string
     * @db
     */
    protected $title;

    /**
     * Configurations.
     *
     * @var string
     * @db text
     */
    protected $configurations;

    /**
     * Import ID if the item is based on an ICS structure.
     *
     * @var string
     * @db
     */
    protected $importId;

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get configurations.
     *
     * @return int[]
     */
    public function getConfigurationIds()
    {
        return GeneralUtility::intExplode(',', $this->configurations);
    }
}
