<?php

namespace HDNET\Calendarize\Event;

final class PluginConfigurationSettingsEvent
{
    private $settings = [];

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }
}
