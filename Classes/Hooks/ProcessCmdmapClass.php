<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Service\ConfigurationRelationService;
use HDNET\Calendarize\Service\IndexerService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook for cmd map processing.
 */
class ProcessCmdmapClass
{
    /**
     * Information about the event (or any other calendarize record) that is currently
     * published or discarded.
     *
     * @var array{key: string, tableName: string, uid: int, liveUid: int}
     */
    protected static array $lastProcessedEvent = [
        'key' => '',
        'tableName' => '',
        'uid' => 0,
        'liveUid' => 0,
    ];

    /**
     * As the versioning process is always handled in the same order - first events, then configurations - we
     * have to save the event, which is handled and do the actions later on the configurations.
     * Reindexing only makes sense after publishing the last configuration!
     */
    public function processCmdmap_preProcess(
        int|string $command,
        string $table,
        int|string $uid,
        mixed $value,
        DataHandler $handler,
        false|array $pasteUpdate,
    ): void {
        if ('version' !== $command) {
            return;
        }

        foreach (Register::getRegister() as $key => $configuration) {
            // we just want the event!
            if ('ConfigurationGroup' === $key || $configuration['tableName'] !== $table) {
                continue;
            }

            switch ($value['action'] ?? '') {
                // We need to save the live-uid for deleting the workspace-indexes
                // after deleting the workspace version
                case 'clearWSID':
                    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                        ->getConnectionForTable($table);

                    // get original version of this record
                    $row = $connection->select(['t3ver_oid'], $table, ['uid' => $uid])
                        ->fetchAssociative();

                    self::$lastProcessedEvent = [
                        'key' => $key,
                        'tableName' => $table,
                        'uid' => (int)$uid,
                        'liveUid' => (int)($row['t3ver_oid'] ?? 0),
                    ];
                    break;
                    // we need to save both values for publishing a deleted record
                case 'publish':
                    self::$lastProcessedEvent = [
                        'key' => $key,
                        'tableName' => $table,
                        'uid' => (int)($value['swapWith'] ?? 0),
                        'liveUid' => (int)$uid,
                    ];
                    break;
            }
        }
    }

    /**
     * Handle CMD.
     */
    public function processCmdmap_postProcess(
        int|string $command,
        string $table,
        int|string $uid,
        mixed $value,
        DataHandler $handler,
        false|array $pasteUpdate,
        array $pasteDatamap,
    ): void {
        /** @var IndexerService $indexer */
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        $workspaceId = (int)$context->getPropertyFromAspect('workspace', 'id');

        $action = \is_array($value) ? (string)($value['action'] ?? '') : '';

        // The configurations of a record are published/discarded after the record itself. As the
        // index is based on the data of the configurations, it has to be rebuilt afterwards.
        if ('version' === $command && ConfigurationRelationService::CONFIGURATION_TABLE === $table) {
            $this->finishVersionAction($indexer, $action, $workspaceId);

            return;
        }

        foreach (Register::getRegister() as $key => $configuration) {
            if ($configuration['tableName'] !== $table) {
                continue;
            }

            if ('version' === $command && \in_array($action, ['publish', 'clearWSID'], true)) {
                // Configurations that are handled internally by the core (e.g. on discard) never
                // show up in the command map, so the index is also updated for the record itself.
                $this->finishVersionAction($indexer, $action, $workspaceId);

                continue;
            }

            // the live-uid is given there. But we need the overlayed one for further process
            if ('delete' === $command && $workspaceId) {
                $wsOverlay = BackendUtility::getRecordWSOL($table, (int)$uid, '', false);
                if ($wsOverlay['_ORIG_uid'] ?? false) {
                    $uid = $wsOverlay['_ORIG_uid'];
                }
            }
            $indexer->reindex($key, $table, (int)$uid);
        }

        // Deleting/restoring a configuration or a configuration group affects all records that use them
        if (\in_array($command, ['delete', 'undelete'], true)) {
            if (ConfigurationRelationService::GROUP_TABLE === $table) {
                $indexer->reindexByConfigurationGroups([(int)$uid]);
            } elseif (ConfigurationRelationService::CONFIGURATION_TABLE === $table) {
                $indexer->reindexByConfigurations([(int)$uid]);
            }
        }
    }

    /**
     * Update the index after a record (or one of its configurations) was published or discarded.
     *
     * In both cases the workspace indexes of the record are obsolete: after publishing the data
     * lives in the live workspace, after discarding the workspace version is gone. The live
     * record itself is reindexed, as its configurations may have changed with the publishing.
     */
    protected function finishVersionAction(IndexerService $indexer, string $action, int $workspaceId): void
    {
        if (!\in_array($action, ['publish', 'clearWSID'], true)) {
            return;
        }

        $event = self::$lastProcessedEvent;
        if ('' === $event['key']) {
            return;
        }

        $configuration = Register::getRegister()[$event['key']] ?? null;
        if (null === $configuration) {
            return;
        }

        // A record that only exists in the workspace has no live counterpart, its indexes are
        // related to the workspace record itself.
        $this->removeWorkspaceIndexes($configuration, $workspaceId, $event['liveUid'] ?: $event['uid']);

        $liveUid = $event['liveUid'] ?: ('publish' === $action ? $event['uid'] : 0);
        if ($liveUid > 0) {
            $indexer->reindex($event['key'], $event['tableName'], $liveUid);
        }
    }

    protected function removeWorkspaceIndexes(array $configuration, int $workspaceId, int $parentId): void
    {
        if (!$workspaceId || !$parentId) {
            return;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(IndexerService::TABLE_NAME);

        $connection->delete(
            IndexerService::TABLE_NAME,
            [
                't3ver_wsid' => $workspaceId,
                'foreign_uid' => $parentId,
                'unique_register_key' => $configuration['uniqueRegisterKey'],
            ],
        );
    }
}
