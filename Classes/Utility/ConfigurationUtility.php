<?php

/**
 * Configuration Utility.
 */
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
     *
     * @var array
     */
    protected static $configuration;

    /**
     * Get the given configuration value.
     *
     * @param string $name
     *
     * @return mixed
     */
    public static function get($name)
    {
        self::loadConfiguration();

        return isset(self::$configuration[$name]) ? self::$configuration[$name] : null;
    }

    /**
     * Load the current configuration.
     */
    protected static function loadConfiguration()
    {
        if (null === self::$configuration) {
            self::$configuration = (array)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('calendarize');
        }
    }
}
