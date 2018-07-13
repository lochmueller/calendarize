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

        $eventClass = \get_class($event);
        foreach (self::$configuration as $configuration) {
            if ($configuration['modelName'] === $eventClass) {
                return $configuration['uniqueRegisterKey'];
            }
            if (isset($configuration['subClasses']) && \is_array($configuration['subClasses'])) {
                foreach ($configuration['subClasses'] as $subClass) {
                    if ($subClass === $eventClass) {
                        return $configuration['uniqueRegisterKey'];
                    }
                }
            }
        }

        throw new Exception('No valid uniqueRegisterKey for: ' . $eventClass, 1236712);
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
