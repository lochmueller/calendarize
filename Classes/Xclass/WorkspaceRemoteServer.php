<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Xclass;

use HDNET\Calendarize\Service\IndexerService;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Workspaces\Controller\Remote\RemoteServer;

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
