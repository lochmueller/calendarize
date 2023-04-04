<?php

/**
 * ExtensionConfiguration Utility.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use HDNET\Calendarize\Register;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

/**
 * ExtensionConfiguration Utility.
 */
class ExtensionConfigurationUtility
{
    /**
     * Configuration cache.
     */
    protected static array $configuration = [];

    /**
     * Get the given configuration value.
     */
    public static function get(string $name): mixed
    {
        self::loadConfiguration();

        return self::$configuration[$name] ?? null;
    }

    /**
     * Return the Unique Register Key by the given EventModel.
     *
     * @throws \Exception
     */
    public static function getUniqueRegisterKeyForModel(DomainObjectInterface $event): string
    {
        self::loadConfiguration();

        $eventClass = get_class($event);
        foreach (self::$configuration as $configuration) {
            if ($configuration['modelName'] === $eventClass) {
                return $configuration['uniqueRegisterKey'];
            }
            if (isset($configuration['subClasses'])
                && is_array($configuration['subClasses'])
                && in_array($eventClass, $configuration['subClasses'])
            ) {
                return $configuration['uniqueRegisterKey'];
            }
        }

        throw new \Exception('No valid uniqueRegisterKey for: ' . $eventClass, 1236712);
    }

    /**
     * Load the current configuration.
     */
    protected static function loadConfiguration(): void
    {
        if (null === self::$configuration) {
            self::$configuration = Register::getRegister();
        }
    }
}
