<?php

/**
 * ExtensionConfiguration Utility.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use Exception;
use HDNET\Calendarize\Register;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

/**
 * ExtensionConfiguration Utility.
 */
class ExtensionConfigurationUtility
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
     * Return the Unique Register Key by the given EventModel.
     *
     * @param DomainObjectInterface $event
     *
     * @throws Exception
     *
     * @return string
     */
    public static function getUniqueRegisterKeyForModel(DomainObjectInterface $event)
    {
        self::loadConfiguration();

        $uniqueRegisterKey = null;
        foreach (self::$configuration as $configuration) {
            if ($configuration['modelName'] === \get_class($event)) {
                $uniqueRegisterKey = $configuration['uniqueRegisterKey'];
                break;
            }
        }

        if (null === $uniqueRegisterKey) {
            throw new Exception('No valid uniqueRegisterKey for: ' . \get_class($event), 1236712);
        }

        return $uniqueRegisterKey;
    }

    /**
     * Load the current configuration.
     */
    protected static function loadConfiguration()
    {
        if (null === self::$configuration) {
            self::$configuration = Register::getRegister();
        }
    }
}
