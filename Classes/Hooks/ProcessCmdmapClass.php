<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Service\IndexerService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook for cmd map processing.
 */
class ProcessCmdmapClass
{
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
        array $pasteDatamap
    ): void {
        /** @var IndexerService $indexer */
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        $workspaceId = $context->getPropertyFromAspect('workspace', 'id');

        $register = Register::getRegister();
        foreach ($register as $key => $configuration) {
            if ('version' == $command && 'swap' == $value['action']) {
                // do nothing with the event itself. The configuration is the last one, which is published
                if ('tx_calendarize_domain_model_configuration' == $table) {
                    $parent = $this->findParentEventInThisTable($configuration['tableName'], (int)$uid);
                    if (\count($parent)) {
                        $parentConfigurations = GeneralUtility::trimExplode(',', $parent['calendarize']);
                        // we just re-index the last given configuration (this is just a workaround - but indexing
                        // of all leads to the behaviour, that only the first one is really indexed)
                        if ($uid == $parentConfigurations[\count($parentConfigurations) - 1]) {
                            $indexer->reindex($key, $configuration['tableName'], (int)$parent['uid']);
                        }
                    }
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

    protected function findParentEventInThisTable(string $table, int $uid): array
    {
        // there is no calendarize field, and we will not find our parent there
        if ('tx_calendarize_domain_model_configurationgroup' == $table) {
            return [];
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);

        return $queryBuilder
            ->select('uid', 'calendarize')
            ->from($table)
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('calendarize', $uid),
                    $queryBuilder->expr()->like(
                        'calendarize',
                        $queryBuilder->createNamedParameter($uid . ',%')
                    ),
                    $queryBuilder->expr()->like(
                        'calendarize',
                        $queryBuilder->createNamedParameter('%,' . $uid)
                    ),
                    $queryBuilder->expr()->like(
                        'calendarize',
                        $queryBuilder->createNamedParameter('%,' . $uid . ',%')
                    )
                )
            )
            ->executeQuery()
            ->fetchAssociative();
    }
}
