<?php

namespace HDNET\Calendarize\Updates;

use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

abstract class AbstractUpdate implements UpgradeWizardInterface
{
    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $title = '';

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getIdentifier(): string
    {
        return static::class;
    }
}
