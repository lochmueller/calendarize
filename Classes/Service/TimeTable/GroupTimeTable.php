<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationGroup;

/**
 * Group service.
 */
class GroupTimeTable extends AbstractTimeTable
{
    /**
     * Modify the given times via the configuration.
     */
    public function handleConfiguration(array &$times, Configuration $configuration): void
    {
        foreach ($configuration->getGroups() as $group) {
            /** @var ConfigurationGroup $group */
            $times = array_merge($times, $this->buildSingleTimeTableByGroup($group));
        }
    }
}
