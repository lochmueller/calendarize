<?php
/**
 * Register the calendarize objects
 *
 * @package Calendarize
 * @author  Tim Lochmüller
 */
namespace HDNET\Calendarize;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Register the calendarize objects
 *
 * @author Tim Lochmüller
 */
class Register {

	/**
	 * Register in the extTables
	 *
	 * @param array $configuration
	 *
	 * @return void
	 */
	static public function extTables(array $configuration) {
		self::createTcaConfiguration($configuration);
		self::registerItem($configuration);
	}

	/**
	 * Add the calendarize to the given TCA
	 *
	 * @param $configuration
	 */
	static protected function createTcaConfiguration($configuration) {
		$tableName = $configuration['tableName'];
		$GLOBALS['TCA'][$tableName]['columns']['calendarize'] = array(
			'label'  => 'Calendarize',
			'config' => array(
				'type'          => 'inline',
				'foreign_table' => 'tx_calendarize_domain_model_configuration',
				'minitems'      => $configuration['required'] ? 1 : 0,
				'maxitems'      => 99,
				'behaviour'     => array(
					'enableCascadingDelete' => TRUE,
				),
			),
		);

		$GLOBALS['TCA'][$tableName]['columns']['calendarize_info'] = array(
			'label'  => 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xml:tca.information',
			'config' => array(
				'type'     => 'user',
				'userFunc' => 'HDNET\\Calendarize\\UserFunction\\TcaInformation->informationField',
			),
		);
		ExtensionManagementUtility::addToAllTCAtypes($tableName, 'calendarize,calendarize_info');
	}

	/**
	 * Internal register
	 *
	 * @param array $configuration
	 */
	static protected function registerItem(array $configuration) {
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize'][$configuration['uniqueRegisterKey']] = $configuration;
	}

	/**
	 * Register in the extLocalconf
	 *
	 * @param array $configuration
	 *
	 * @return void
	 */
	static public function extLocalconf(array $configuration) {
		self::registerItem($configuration);
	}

	/**
	 * Get the EXT:autoloader default configuration
	 *
	 * @return array
	 */
	static public function getDefaultAutoloader() {
		return array(
			'Hooks',
			'Slots',
			'SmartObjects',
			'FlexForms',
			'CommandController',
			'StaticTyposcript'
		);
	}

	/**
	 * Get the register
	 *
	 * @return array
	 */
	static public function getRegister() {
		return is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize']) ? $GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize'] : array();
	}

	/**
	 * Default configuration for the current extension.
	 * If you want to use the calendarize features in your own extension,
	 * you have to implement a own configuration.
	 *
	 * @return array
	 */
	static public function getDefaultCalendarizeConfiguration() {
		$configuration = array(
			'uniqueRegisterKey' => 'Event',
			'title'             => 'Calendarize Event',
			'modelName'         => 'HDNET\\Calendarize\\Domain\\Model\\Event',
			'partialIdentifier' => 'Event',
			'tableName'         => 'tx_calendarize_domain_model_event',
			'required'          => TRUE,
		);
		return $configuration;
	}

}