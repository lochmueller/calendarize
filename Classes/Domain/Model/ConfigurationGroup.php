<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Logical configuration group.
 */
class ConfigurationGroup extends AbstractModel
{
    use ImportTrait;

    protected string $title = '';

    protected string $configurations = '';

    protected bool $hidden = false;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Get configuration ids.
     */
    public function getConfigurationIds(): array
    {
        return GeneralUtility::intExplode(',', $this->configurations);
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }
}
