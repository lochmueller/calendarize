<?php

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
     * Get the given configuration value.
     */
    public static function get(string $name): ?array
    {
        return Register::getRegister()[$name] ?? null;
    }

    /**
     * Return the Unique Register Key by the given EventModel.
     *
     * @throws \Exception
     */
    public static function getUniqueRegisterKeyForModel(DomainObjectInterface $event): string
    {
        return self::getConfigurationForModel($event)['uniqueRegisterKey'];
    }

    public static function getConfigurationForModel(DomainObjectInterface $event): array
    {
        $eventClass = $event::class;
        foreach (Register::getRegister() as $configuration) {
            if ($configuration['modelName'] === $eventClass) {
                return $configuration;
            }
            if (\in_array($eventClass, $configuration['subClasses'] ?? [], true)) {
                return $configuration;
            }
        }

        throw new \Exception('No valid uniqueRegisterKey for: ' . $eventClass, 1236712);
    }
}
