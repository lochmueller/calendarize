<?php
/**
 * Group service
 *
 */
namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationGroup;

/**
 * Group service
 *
 */
class GroupTimeTable extends AbstractTimeTable
{

    /**
     * Modify the given times via the configuration
     *
     * @param array         $times
     * @param Configuration $configuration
     *
     * @return void
     */
    public function handleConfiguration(array &$times, Configuration $configuration)
    {
        foreach ($configuration->getGroups() as $group) {
            /** @var ConfigurationGroup $group */
            $times = array_merge($times, $this->buildSingleTimeTableByGroup($group));
        }
    }
}
