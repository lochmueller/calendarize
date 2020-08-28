<?php

/**
 * Helper Utility.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use function get_class;
use function is_object;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Helper Utility.
 */
class HelperUtility
{

    /**
     * Get the query for the given class name oder object.
     *
     * @param string|object $objectName
     *
     * @return QueryInterface
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public static function getQuery($objectName)
    {
        $objectName = is_object($objectName) ? get_class($objectName) : $objectName;
        /** @var PersistenceManagerInterface $manager */
        static $manager = null;
        if (null === $manager) {
            $manager = GeneralUtility::makeInstance(ObjectManager::class)->get(PersistenceManagerInterface::class);
        }

        return $manager->createQueryForType($objectName);
    }

    /**
     * Get the signal slot dispatcher.
     *
     * @return Dispatcher
     */
    public static function getSignalSlotDispatcher(): Dispatcher
    {
        return GeneralUtility::makeInstance(Dispatcher::class);
    }

    /**
     * Create a flash message.
     *
     * @param string $message
     * @param string $title
     * @param int    $mode
     *
     * @throws Exception
     */
    public static function createFlashMessage($message, $title = '', $mode = FlashMessage::OK)
    {
        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $mode, true);
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->enqueue($flashMessage);
    }

    /**
     * Get the database connection.
     *
     * @param mixed $table
     *
     * @return Connection
     */
    public static function getDatabaseConnection($table)
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
    }
}
