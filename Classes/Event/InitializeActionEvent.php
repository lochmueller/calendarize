<?php

namespace HDNET\Calendarize\Event;

use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Request;

final class InitializeActionEvent
{
    public function __construct(
        private Request $request,
        private Arguments $arguments,
        private array $settings,
        private readonly string $className,
        private readonly string $actionName,
    ) {}

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
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
