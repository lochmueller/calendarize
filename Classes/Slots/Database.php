<?php
/**
 * Create the needed database fields
 *
 * @package Calendarize\Slots
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Slots;

use HDNET\Calendarize\Register;

/**
 * Create the needed database fields
 *
 * @author Tim Lochmüller
 */
class Database {

	/**
	 * Add the smart object SQL string the the signal below
	 *
	 * @signalClass \TYPO3\CMS\Install\Service\SqlExpectedSchemaService
	 * @signalName tablesDefinitionIsBeingBuilt
	 *
	 * @param array $sqlString
	 *
	 * @return array
	 */
	public function loadCalendarizeTables(array $sqlString) {
		$sqlString[] = $this->getCalendarizeDatabaseString();
		return array('sqlString' => $sqlString);
	}

	/**
	 * Get  the calendarize string for the registered tables
	 *
	 * @return string
	 */
	protected function getCalendarizeDatabaseString() {
		$sql = array();
		foreach (Register::getRegister() as $configuration) {
			$sql[] = 'CREATE TABLE ' . $configuration['tableName'] . ' (
			calendarize tinytext
			);';
		}
		return implode(LF, $sql);
	}

	/**
	 * Add the smart object SQL string the the signal below
	 *
	 * @signalClass \TYPO3\CMS\Extensionmanager\Utility\InstallUtility
	 * @signalName tablesDefinitionIsBeingBuilt
	 *
	 * @param array  $sqlString
	 * @param string $extensionKey
	 *
	 * @return array
	 */
	public function updateCalendarizeTables(array $sqlString, $extensionKey) {
		$sqlString[] = $this->getCalendarizeDatabaseString();
		return array(
			'sqlString'    => $sqlString,
			'extensionKey' => $extensionKey
		);
	}
} 