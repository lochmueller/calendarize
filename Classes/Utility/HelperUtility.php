<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     */
    public static function getQuery(string|object $objectName): QueryInterface
    {
        $objectName = \is_object($objectName) ? $objectName::class : $objectName;
        /** @var PersistenceManagerInterface $manager */
        static $manager = null;
        if (null === $manager) {
            $manager = GeneralUtility::makeInstance(PersistenceManagerInterface::class);
        }

        return $manager->createQueryForType($objectName);
    }

    /**
     * Create a flash message.
     *
     * @throws Exception
     */
    public static function createFlashMessage(
        string $message,
        string $title = '',
        ContextualFeedbackSeverity $mode = ContextualFeedbackSeverity::OK
    ): void {
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
     * @throws Exception
     */
    public static function createTranslatedFlashMessage(
        string $messageKey,
        string $titleKey = '',
        ContextualFeedbackSeverity $mode = ContextualFeedbackSeverity::OK
    ): void {
        try {
            $message = LocalizationUtility::translate($messageKey, 'calendarize') ?? $messageKey;
            $title = LocalizationUtility::translate($titleKey, 'calendarize') ?? $titleKey;
        } catch (\TypeError $exception) {
            $message = $messageKey;
            $title = $titleKey;
        }
        self::createFlashMessage($message, $title, $mode);
    }

    /**
     * Create a flash message with a translated title.
     *
     * @throws Exception
     */
    public static function createTranslatedTitleFlashMessage(
        string $message,
        string $titleKey = '',
        ContextualFeedbackSeverity $mode = ContextualFeedbackSeverity::OK
    ): void {
        try {
            $title = LocalizationUtility::translate($titleKey, 'calendarize') ?? $titleKey;
        } catch (\TypeError $exception) {
            $title = $titleKey;
        }
        self::createFlashMessage($message, $title, $mode);
    }

    /**
     * Get the database connection.
     */
    public static function getDatabaseConnection(string $table): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
    }

    /**
     * Get the database connection.
     */
    public static function getQueryBuilder(string $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }

    /**
     * Gets user object by context.
     * This class is also used in install tool, where $GLOBALS['BE_USER'] is not set and can be null.
     */
    protected static function getUserByContext(): ?AbstractUserAuthentication
    {
        if (
            ($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController
            && $GLOBALS['TSFE']->fe_user instanceof FrontendUserAuthentication
        ) {
            return $GLOBALS['TSFE']->fe_user;
        }

        return $GLOBALS['BE_USER'] ?? null;
    }

    /**
     * Returns a query with replaced params.
     */
    public static function queryWithParams(QueryBuilder $queryBuilder): string
    {
        $query = $queryBuilder->getSQL();
        $params = $queryBuilder->getParameters();
        krsort($params);

        foreach ($params as $key => $value) {
            if (!\is_float($value)) {
                $value = '"' . $value . '"';
            }
            $query = str_replace(':' . $key, $value, $query);
        }

        return $query;
    }
}
