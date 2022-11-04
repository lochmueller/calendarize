<?php

namespace HDNET\Calendarize\Updates;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

abstract class AbstractUpdate implements ChattyInterface, UpgradeWizardInterface
{
    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $title = '';

     /**
     * @var OutputInterface
     */
    protected $output;

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }
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
