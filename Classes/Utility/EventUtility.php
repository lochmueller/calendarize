<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Event utility.
 */
class EventUtility
{
    /**
     * Get the original record by configuration.
     *
     * @param array $configuration
     * @param int   $uid
     *
     * @return DomainObjectInterface|null
     */
    public static function getOriginalRecordByConfiguration(array $configuration, int $uid): ?DomainObjectInterface
    {
        $modelName = $configuration['modelName'];

        $query = HelperUtility::getQuery($modelName);
        if (self::isIgnoreEnableFields()) {
            $query->getQuerySettings()->setIgnoreEnableFields(true);
        }

        $query->getQuerySettings()
            ->setRespectStoragePage(false);
        $query->getQuerySettings()
            ->setRespectSysLanguage(false);
        $query->matching($query->equals('uid', $uid));

        return $query->execute()
            ->getFirst();
    }

    /**
     * Get the original record by configuration.
     * For backend only!
     *
     * @param array $configuration
     * @param int   $uid           live uid or (correct) versioned uid of record
     * @param int   $workspaceId   the workspace ID to get the record of (-99 to get it from the current BE user)
     *
     * @return DomainObjectInterface|null
     */
    public static function getOriginalRecordByConfigurationInWorkspace(
        array $configuration,
        int $uid,
        int $workspaceId,
    ): ?DomainObjectInterface {
        $table = $configuration['tableName'];
        $modelName = $configuration['modelName'];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();

        $queryBuilder->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)),
            );

        $row = $queryBuilder->executeQuery()->fetchAssociative();

        if (false === $row) {
            return null;
        }

        // If it is already the correct version, skip overlay
        if ($row['t3ver_wsid'] !== $workspaceId) {
            BackendUtility::workspaceOL($table, $row, $workspaceId);
            // Swap the UIDs back, since the overlay changes the id to the live UID.
            // We require the versioned UID for the dataMapper.
            $row['uid'] = !empty($row['_ORIG_uid']) ? $row['_ORIG_uid'] : $row['uid'];
        }

        /** @var DataMapper $dataMapper */
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);

        return $dataMapper->map($modelName, [$row])[0];
    }

    protected static function isIgnoreEnableFields(): bool
    {
        // Regular CLI requests
        if (Environment::isCli()) {
            return true;
        }

        // Modern (TYPO3_REQUEST) Backend request
        if (
            ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()
        ) {
            return true;
        }

        // Old backend request (e.g. install tool wizards)
        if (\defined('TYPO3') && TYPO3 == 'BE') {
            return true;
        }

        return false;
    }
}
