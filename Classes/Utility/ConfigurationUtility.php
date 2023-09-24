<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Configuration Utility.
 */
class ConfigurationUtility
{
    /**
     * Configuration cache.
     */
    protected static ?array $configuration = null;

    /**
     * Get the given configuration value.
     */
    public static function get(string $name): mixed
    {
        self::loadConfiguration();

        return self::$configuration[$name] ?? null;
    }

    /**
     * Load the current configuration.
     */
    protected static function loadConfiguration(): void
    {
        if (null === self::$configuration) {
            self::$configuration = (array)GeneralUtility::makeInstance(ExtensionConfiguration::class)
                ->get('calendarize');
        }
    }
}
