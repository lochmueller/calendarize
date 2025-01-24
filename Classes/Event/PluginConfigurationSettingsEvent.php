<?php

namespace HDNET\Calendarize\Event;

final class PluginConfigurationSettingsEvent
{
    public function __construct(
        private array $settings,
    ) {}

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }
}
