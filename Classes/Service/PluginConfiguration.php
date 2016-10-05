<?php
/**
 * Service for Plugin configurations
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Register;

/**
 * Service for Plugin configurations
 *
 * @author Tim Lochmüller
 */
class PluginConfiguration extends AbstractService
{

    /**
     * Add the configurations to the given Plugin configuration
     *
     * @param array $config
     *
     * @return array
     */
    public function addConfig($config)
    {
        foreach (Register::getRegister() as $key => $configuration) {
            $config['items'][] = [
                $configuration['title'],
                $key,
            ];
        }
        return $config;
    }
}
