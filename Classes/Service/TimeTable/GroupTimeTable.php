<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationGroup;
use HDNET\Calendarize\Domain\Repository\ConfigurationGroupRepository;

/**
 * Group service.
 */
class GroupTimeTable extends AbstractTimeTable
{
    protected ConfigurationGroupRepository $configurationGroupRepository;

    public function injectConfigurationGroupRepository(ConfigurationGroupRepository $configurationGroupRepository): void
    {
        $this->configurationGroupRepository = $configurationGroupRepository;
    }

    /**
     * Modify the given times via the configuration.
     */
    public function handleConfiguration(array &$times, Configuration $configuration): void
    {
        $groupUids = [];
        foreach ($configuration->getGroups() as $group) {
            /** @var ConfigurationGroup $group */
            $groupUids[] = (int)$group->getUid();
        }

        // Reload the groups with respected enable fields (the lazy relation ignores them in backend context)
        $enabledGroups = [];
        foreach ($this->configurationGroupRepository->findByUidsRespectingEnableFields($groupUids) as $group) {
            $enabledGroups[$group->getUid()] = $group;
        }

        // Keep the order of the relation
        foreach ($groupUids as $groupUid) {
            if (isset($enabledGroups[$groupUid])) {
                $times = array_merge($times, $this->buildSingleTimeTableByGroup($enabledGroups[$groupUid]));
            }
        }
    }
}
