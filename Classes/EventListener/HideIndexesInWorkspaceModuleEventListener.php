<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

use TYPO3\CMS\Workspaces\Event\AfterCompiledCacheableDataForWorkspaceEvent;

class HideIndexesInWorkspaceModuleEventListener
{
    protected const INDEX_TABLE = 'tx_calendarize_domain_model_index';

    public function __invoke(AfterCompiledCacheableDataForWorkspaceEvent $event)
    {
        // unset all indexes in data
        $data = $event->getData();
        foreach ($data as $key => $value) {
            if (str_starts_with($key, self::INDEX_TABLE)) {
                unset($data[$key]);
            }
        }
        $event->setData($data);

        // unset all indexes in versions
        $versions = $event->getVersions();
        unset($versions[self::INDEX_TABLE]);
        $event->setVersions($versions);
    }
}
