<?php

/**
 * Logical configuration group.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Logical configuration group.
 */
class ConfigurationGroup extends AbstractModel
{
    use ImportTrait;
    /**
     * Title.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Configurations.
     *
     * @var string
     */
    protected $configurations = '';

    /**
     * Hidden.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Get configurations.
     *
     * @return int[]
     */
    public function getConfigurationIds(): array
    {
        return GeneralUtility::intExplode(',', $this->configurations);
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }
}
