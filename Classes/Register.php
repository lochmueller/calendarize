<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
namespace HDNET\Calendarize;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class Register {

	/**
	 * @param array $configuration
	 */
	static public function extTables(array $configuration) {
		self::register($configuration);
	}

	/**
	 * @param array $configuration
	 */
	static public function extLocalconf(array $configuration) {
		self::register($configuration);
	}

	/**
	 * @param array $configuration
	 */
	static protected function register(array $configuration) {
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize'][$configuration['uniqueRegisterKey']] = $configuration;
	}

	/**
	 * Get the register
	 *
	 * @return array
	 */
	static public function getRegister() {
		return $GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize'] ? : array();
	}

	/**
	 * Default configuration for the current extension.
	 * If you want to use the calendarize features in your own extension, you hav to implement a own configuration.
	 *
	 * @return array
	 */
	static public function getDefaultCalendarizeConfiguration() {
		$configuration = array(
			'uniqueRegisterKey' => 'Calendarize Event',
			'modelName'         => 'HDNET\\Calendarize\\Domain\\Model\\Event',
			'partialIdentifier' => 'Event',
			'tableName'         => 'tx_calendarize_domain_model_event',
			'required'          => TRUE,
		);
		return $configuration;
	}

}
 