<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Xclass;

use HDNET\Calendarize\Service\IndexerService;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Workspaces\Controller\Remote\RemoteServer;

if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 10) {
    // TYPO3 v11 and after
    class WorkspaceRemoteServer extends RemoteServer
    {
        /**
         * Get List of workspace changes.
         *
         * @param \stdClass $parameter
         *
         * @return array $data
         */
        public function getWorkspaceInfos($parameter)
        {
            // To avoid too much work we use -1 to indicate that every page is relevant
            $pageId = $parameter->id > 0 ? $parameter->id : -1;
            if (!isset($parameter->language) || !MathUtility::canBeInterpretedAsInteger($parameter->language)) {
                $parameter->language = null;
            }
            if (!isset($parameter->stage) || !MathUtility::canBeInterpretedAsInteger($parameter->stage)) {
                // -99 disables stage filtering
                $parameter->stage = -99;
            }
            $versions = $this->workspaceService->selectVersionsInWorkspace(
                $this->getCurrentWorkspace(),
                $parameter->stage,
                $pageId,
                $parameter->depth,
                'tables_select',
                $parameter->language
            );

            // Drop Index Table from View
            if (isset($versions[IndexerService::TABLE_NAME])) {
                unset($versions[IndexerService::TABLE_NAME]);
            }

            $data = $this->gridDataService->generateGridListFromVersions($versions, $parameter, $this->getCurrentWorkspace());

            return $data;
        }
    }
} else {
    // TYPO3 v10
    class WorkspaceRemoteServer extends RemoteServer
    {
        public function getWorkspaceInfos($parameter)
        {
            // To avoid too much work we use -1 to indicate that every page is relevant
            $pageId = $parameter->id > 0 ? $parameter->id : -1;
            if (!isset($parameter->language) || !MathUtility::canBeInterpretedAsInteger($parameter->language)) {
                $parameter->language = null;
            }
            $versions = $this->workspaceService->selectVersionsInWorkspace($this->getCurrentWorkspace(), 0, -99, $pageId, $parameter->depth, 'tables_select', $parameter->language);

            // Drop Index Table from View
            if (isset($versions[IndexerService::TABLE_NAME])) {
                unset($versions[IndexerService::TABLE_NAME]);
            }

            $data = $this->gridDataService->generateGridListFromVersions($versions, $parameter, $this->getCurrentWorkspace());

            return $data;
        }
    }
}
