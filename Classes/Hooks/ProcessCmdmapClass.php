<?php

/**
 * Hook for cmd map processing.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Hooks;

use HDNET\Autoloader\Annotation\Hook;
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
 *
 * @Hook("TYPO3_CONF_VARS|SC_OPTIONS|t3lib/class.t3lib_tcemain.php|processCmdmapClass")
 */
class ProcessCmdmapClass extends AbstractHook
{
    /**
     * Handle CMD.
     *
     * @param string      $command
     * @param string      $table
     * @param int         $uid
     * @param mixed       $value
     * @param DataHandler $handler
     * @param mixed       $pasteUpdate
     * @param mixed       $pasteDatamap
     */
    public function processCmdmap_postProcess($command, $table, $uid, $value, $handler, $pasteUpdate, $pasteDatamap)
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $workspaceId = $context->getPropertyFromAspect('workspace', 'id');

        $register = Register::getRegister();
        foreach ($register as $key => $configuration) {
            if ('version' == $command && 'swap' == $value['action']) {
                // do nothing with the event itself. The configuration is the last one, which is published
                if ('tx_calendarize_domain_model_configuration' == $table) {
                    $parent = $this->findParentEventInThisTable($configuration['tableName'], (int)$uid);
                    if (\is_array($parent)) {
                        $parentConfigurations = GeneralUtility::trimExplode(',', $parent['calendarize']);
                        // we just re-index the last given configuration (this is just a workaround - but indexing of all leads
                        // to the behaviour, that only the first one is really indexed)
                        if ($uid == $parentConfigurations[\count($parentConfigurations) - 1]) {
                            $indexer = GeneralUtility::makeInstance(IndexerService::class);
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

                $indexer = GeneralUtility::makeInstance(IndexerService::class);
                $indexer->reindex($key, $table, (int)$uid);
            }
        }
    }

    protected function findParentEventInThisTable($table, $uid)
    {
        // there is no calendarize field and we will not find our parent there
        if ('tx_calendarize_domain_model_configurationgroup' == $table) {
            return false;
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table)->createQueryBuilder();

        return $queryBuilder->select('uid', 'calendarize')
            ->from($table)
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('calendarize', $uid),
                    $queryBuilder->expr()->like('calendarize', $queryBuilder->createNamedParameter($uid . ',%')),
                    $queryBuilder->expr()->like('calendarize', $queryBuilder->createNamedParameter('%,' . $uid)),
                    $queryBuilder->expr()->like('calendarize', $queryBuilder->createNamedParameter('%,' . $uid . ',%'))
                )
            )->execute()->fetchAssociative();
    }
}
