<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Register;
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
    protected static int $lastProcessedEventId = 0;
    protected static int $lastProcessedEventIdLive = 0;

    /**
     * As the versioning process is always handles in the same order - first events, then configurations - we
     * have to save the event-ids, which are handled and do the actions later on the configurations.
     * Reindexing only make sense after publishing the last configuration!
     */
    public function processCmdmap_preProcess(
        int|string $command,
        string $table,
        int|string $uid,
        mixed $value,
        DataHandler $handler,
        false|array $pasteUpdate,
    ): void {
        if($command === 'version'){
            $register = Register::getRegister();

            foreach ($register as $key => $configuration) {
                // we just want the event!
                if($key === 'ConfigurationGroup'){
                    continue;
                }

                // if it is any event
                if($configuration['tableName'] === $table){
                    switch($value['action'] ?? ''){
                        // We need to save the live-uid for deleting the workspace-indexes
                        // after deleting the workspace version
                        case 'clearWSID':
                            $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                                ->getConnectionForTable($table);

                            // get original version of this record
                            $row = $connection->select(['t3ver_oid'], $table, ['uid' => $uid])
                                ->fetchAssociative();

                            self::$lastProcessedEventId = $uid;
                            self::$lastProcessedEventIdLive = $row['t3ver_oid'] ?? 0;
                            break;
                        // we need to save both values for publishing a deleted record
                        case 'publish':
                            self::$lastProcessedEventId = $value['swapWith'] ?? 0;
                            self::$lastProcessedEventIdLive = $uid;
                            break;
                    }
                }
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
        $workspaceId = $context->getPropertyFromAspect('workspace', 'id');

        $register = Register::getRegister();

        $action = $value['action'] ?? '';

        foreach ($register as $key => $configuration) {
            if ('version' === $command && 'tx_calendarize_domain_model_configuration' === $table) {
                if('publish' === $action){
                    $indexer->reindex($key, $configuration['tableName'], self::$lastProcessedEventIdLive);
                    $this->removeWorkspaceIndexes($configuration, $workspaceId, self::$lastProcessedEventIdLive);
                } elseif ('clearWSID' === $action){
                    $this->removeWorkspaceIndexes($configuration, $workspaceId, self::$lastProcessedEventIdLive);
                }
            } elseif ($configuration['tableName'] === $table) {
                // the live-uid is given there. But we need the overlayed one for further process
                if ('delete' === $command && $workspaceId) {
                    $wsOverlay = BackendUtility::getRecordWSOL($table, $uid, '', false);
                    if ($wsOverlay['_ORIG_uid']) {
                        $uid = $wsOverlay['_ORIG_uid'];
                    }
                }
                $indexer->reindex($key, $table, (int)$uid);
            }
        }
    }

    protected function removeWorkspaceIndexes(array $configuration, int $workspaceId, int $parentId) : void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_calendarize_domain_model_index');

        $connection->delete(
            'tx_calendarize_domain_model_index',
            ['t3ver_wsid' => $workspaceId, 'foreign_uid' => $parentId, 'unique_register_key' => $configuration['uniqueRegisterKey']]
        );
    }
}
