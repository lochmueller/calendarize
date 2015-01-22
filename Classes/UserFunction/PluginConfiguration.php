<?php
/**
 * UserFunctions for Plugin configurations
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage UserFunction
 * @author     Tim Lochmüller
 */

namespace HDNET\Calendarize\UserFunction;

use HDNET\Calendarize\Register;

/**
 * UserFunctions for Plugin configurations
 *
 * @package    Calendarize
 * @subpackage UserFunction
 * @author     Tim Lochmüller
 */
class PluginConfiguration {

	/**
	 * Add the configurations to the given Plugin configuration
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function addConfigurations($config) {
		foreach (Register::getRegister() as $key => $configuration) {
			$config['items'][] = array(
				$configuration['title'],
				$key,
			);
		}
		return $config;
	}
}