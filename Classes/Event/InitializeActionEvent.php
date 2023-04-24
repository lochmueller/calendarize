<?php

namespace HDNET\Calendarize\Event;

use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;

final class InitializeActionEvent
{
    public function __construct(
        private Arguments $arguments,
        private array $settings,
        private readonly string $className,
        private readonly string $actionName
    ) {
    }

    public function getArguments(): Arguments
    {
        return $this->arguments;
    }

    public function setArguments(Arguments $arguments): void
    {
        $this->arguments = $arguments;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }
}
