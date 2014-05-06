<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Slots;

use HDNET\Calendarize\Service\RegisterService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class TcaOverridesSlot {

	/**
	 * Add TCA information
	 *
	 * @signalClass \TYPO3\CMS\Core\Utility\ExtensionManagementUtility
	 * @signalName tcaIsBeingBuilt
	 */
	public function overrideTca($tca) {

		$registerService = new RegisterService();
		$tableNames = $registerService->getRegister();

		foreach ($tableNames as $tableName) {
			// DebuggerUtility::var_dump($tca[$tableName]);
		}

		return array($tca);
	}

}
 