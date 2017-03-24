<?php
/**
 * ExtensionConfiguration Utility
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Utility;

use HDNET\Calendarize\Register;
use Exception;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

/**
 * ExtensionConfiguration Utility
 *
 * @author Tim Lochmüller
 */
class ExtensionConfigurationUtility
{

    /**
     * Configuration cache
     *
     * @var array
     */
    protected static $configuration;

    /**
     * Get the given configuration value
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
     * @return string
     * @throws Exception
     */
    public static function getUniqueRegisterKeyForModel(DomainObjectInterface $event)
    {
        self::loadConfiguration();

        $uniqueRegisterKey = null;
        foreach (self::$configuration as $configuration) {
            if ($configuration['modelName'] === get_class($event)) {
                $uniqueRegisterKey = $configuration['uniqueRegisterKey'];
                break;
            }
        }

        if ($uniqueRegisterKey === null) {
            throw new Exception('No valid uniqueRegisterKey for: ' . get_class($event), 1236712);
        }

        return $uniqueRegisterKey;
    }

    /**
     * Load the current configuration
     */
    protected static function loadConfiguration()
    {
        if (self::$configuration === null) {
            self::$configuration = Register::getRegister();
        }
    }
}
