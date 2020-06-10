<?php

/**
 * Logical configuration group.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\SmartExclude;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Logical configuration group.
 *
 * @DatabaseTable
 * @SmartExclude("Language")
 */
class ConfigurationGroup extends AbstractModel
{
    /**
     * Title.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $title;

    /**
     * Configurations.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $configurations;

    /**
     * Import ID if the item is based on an ICS structure.
     *
     * @var string
     * @DatabaseField("string")
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
