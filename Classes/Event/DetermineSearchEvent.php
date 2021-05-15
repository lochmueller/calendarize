<?php

namespace HDNET\Calendarize\Event;

final class DetermineSearchEvent
{
    private $variables = [];

    private $settings = [];

    public function __construct(array $variables, array $settings)
    {
        $this->variables = $variables;
        $this->settings = $settings;
    }

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
