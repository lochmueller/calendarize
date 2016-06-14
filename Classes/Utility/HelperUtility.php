<?php
/**
 * Helper Utility
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Utility;

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Helper Utility
 *
 * @author Tim Lochmüller
 */
class HelperUtility
{

    /**
     * Create a object with the given class name
     * Please use GeneralUtility::makeInstance if you do not need DI
     *
     * @param string $className
     *
     * @return object
     */
    public static function create($className)
    {
        $arguments = func_get_args();
        $objManager = new ObjectManager();
        return call_user_func_array([
            $objManager,
            'get'
        ], $arguments);
    }

    /**
     * Get the query for the given class name oder object
     *
     * @param string|object $objectName
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    public static function getQuery($objectName)
    {
        $objectName = is_object($objectName) ? get_class($objectName) : $objectName;
        /** @var PersistenceManagerInterface $manager */
        static $manager = null;
        if ($manager === null) {
            $manager = self::create(PersistenceManagerInterface::class);
        }
        return $manager->createQueryForType($objectName);
    }

    /**
     * Get the signal slot dispatcher
     *
     * @return Dispatcher
     */
    public static function getSignalSlotDispatcher()
    {
        return self::create(Dispatcher::class);
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
    public static function createFlashMessage($message, $title = '', $mode = FlashMessage::OK)
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $message, $title, $mode, true);
        $class = 'TYPO3\\CMS\\Core\\Messaging\\FlashMessageService';
        /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
        $flashMessageService = GeneralUtility::makeInstance($class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->enqueue($flashMessage);
    }

    /**
     * Get the database connection
     *
     * @return DatabaseConnection
     */
    public static function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
