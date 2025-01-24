<?php

namespace HDNET\Calendarize\Event;

final class DetermineSearchEvent
{
    public function __construct(
        private array $variables,
        private readonly array $settings,
    ) {}

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }
}
