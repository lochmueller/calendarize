<?php
/**
 * Exclude service
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationGroup;

/**
 * Exclude service
 *
 * @author Tim Lochmüller
 */
class ExcludeTimeTable extends AbstractTimeTable
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
        $excludeTimes = [];
        foreach ($configuration->getGroups() as $group) {
            /** @var ConfigurationGroup $group */
            $excludeTimes = array_merge($excludeTimes, $this->buildSingleTimeTableByGroup($group));
        }
        $times = $this->checkAndRemoveTimes($times, $excludeTimes);
    }
}
