<?php
/**
 * Helper Utility
 *
 * @package Calendarize\Utility
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Utility;

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Helper Utility
 *
 * @author Tim Lochmüller
 */
class HelperUtility {

	/**
	 * Create a object with the given class name
	 *
	 * @param string $className
	 *
	 * @return object
	 */
	public static function create($className) {
		$arguments = func_get_args();
		$objManager = new ObjectManager();
		return call_user_func_array(array(
			$objManager,
			'get'
		), $arguments);
	}

	/**
	 * Get the query for the given class name oder object
	 *
	 * @param string|object $objectName
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
	 */
	public static function getQuery($objectName) {
		$objectName = is_object($objectName) ? get_class($objectName) : $objectName;
		/** @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $manager */
		static $manager = NULL;
		if ($manager === NULL) {
			$manager = self::create('TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface');
		}
		return $manager->createQueryForType($objectName);
	}

	/**
	 * Get the signal slot dispatcher
	 *
	 * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	public static function getSignalSlotDispatcher() {
		return self::create('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
	}

	/**
	 * Create a flash message
	 *
	 * @param string $message
	 * @param string $title
	 * @param int    $mode
	 *
	 * @throws \TYPO3\CMS\Core\Exception
	 */
	public static function createFlashMessage($message, $title = '', $mode = FlashMessage::OK) {
		/** @var FlashMessage $flashMessage */
		$flashMessage = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $message, $title, $mode, TRUE);
		$class = 'TYPO3\\CMS\\Core\\Messaging\\FlashMessageService';
		/** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
		$flashMessageService = GeneralUtility::makeInstance($class);
		$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
		$defaultFlashMessageQueue->enqueue($flashMessage);
	}

	/**
	 * Get the database connection
	 *
	 * @return DatabaseConnection
	 */
	static public function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}