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
            if ($configuration['tableName'] === $table) {
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
}
