<?php

/**
 * Helper Utility.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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
     *
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public static function getQuery($objectName)
    {
        $objectName = \is_object($objectName) ? \get_class($objectName) : $objectName;
        /** @var PersistenceManagerInterface $manager */
        static $manager = null;
        if (null === $manager) {
            $manager = GeneralUtility::makeInstance(ObjectManager::class)->get(PersistenceManagerInterface::class);
        }

        return $manager->createQueryForType($objectName);
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
    public static function createFlashMessage(string $message, string $title = '', int $mode = FlashMessage::OK): void
    {
        // Don't store flash messages in CLI context
        // Note: the getUserByContext check is only required for TYPO3 v10 and is fixed in v11 (94418).
        $storeInSession = !Environment::isCli() && null !== self::getUserByContext();
        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $mode, $storeInSession);
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->enqueue($flashMessage);
    }

    /**
     * Create a translated flash message.
     *
     * @param string $messageKey
     * @param string $titleKey
     * @param int    $mode
     *
     * @throws Exception
     */
    public static function createTranslatedFlashMessage(string $messageKey, string $titleKey = '', int $mode = FlashMessage::OK): void
    {
        try {
            $message = LocalizationUtility::translate($messageKey, 'calendarize') ?? $messageKey;
            $title = LocalizationUtility::translate($titleKey, 'calendarize') ?? $titleKey;
        } catch (\TypeError $e) {
            $message = $messageKey;
            $title = $titleKey;
        }
        self::createFlashMessage($message, $title, $mode);
    }

    /**
     * Create a flash message with a translated title.
     *
     * @param string $messageKey
     * @param string $titleKey
     * @param int    $mode
     *
     * @throws Exception
     */
    public static function createTranslatedTitleFlashMessage(string $message, string $titleKey = '', int $mode = FlashMessage::OK): void
    {
        try {
            $title = LocalizationUtility::translate($titleKey, 'calendarize') ?? $titleKey;
        } catch (\TypeError $e) {
            $title = $titleKey;
        }
        self::createFlashMessage($message, $title, $mode);
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

    /**
     * Gets user object by context.
     * This class is also used in install tool, where $GLOBALS['BE_USER'] is not set and can be null.
     *
     * @return AbstractUserAuthentication|null
     */
    protected static function getUserByContext(): ?AbstractUserAuthentication
    {
        if (($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController && $GLOBALS['TSFE']->fe_user instanceof FrontendUserAuthentication) {
            return $GLOBALS['TSFE']->fe_user;
        }

        return $GLOBALS['BE_USER'] ?? null;
    }

    /**
     * _queryWiParms(): Returns a query with replaced params
     *
     * @param object $queryBuilder
     * @return  string  $query
     * @version 0.0.1
     * @since   0.0.1
     */
    public static function queryWithParams($queryBuilder)
    {
        $query = $queryBuilder->getSQL();
        $params = $queryBuilder->getParameters();
        krsort($params);
//    var_dump(__METHOD__, __LINE__, $query, $params);
//    die();
        foreach ($params as $key => $value) {
            if (!is_double($value)) {
                $value = '"' . $value . '"';
            }
            $query = str_replace(':' . $key, $value, $query);
        }
        return $query;
    }
}
