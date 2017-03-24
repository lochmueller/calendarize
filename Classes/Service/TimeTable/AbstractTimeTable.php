<?php
/**
 * Abstract time table service
 *
 * @author  Tim Lochmüller
 */
namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationGroup;
use HDNET\Calendarize\Service\AbstractService;

/**
 * Abstract time table service
 *
 * @author Tim Lochmüller
 */
abstract class AbstractTimeTable extends AbstractService
{

    /**
     * Seconds of 23:59:59 that mark the day end
     */
    const DAY_END = 86399;

    /**
     * Time table service
     *
     * @var \HDNET\Calendarize\Service\TimeTableService
     * @inject
     */
    protected $timeTableService;

    /**
     * Modify the given times via the configuration
     *
     * @param array         $times
     * @param Configuration $configuration
     *
     * @return void
     */
    abstract public function handleConfiguration(array &$times, Configuration $configuration);

    /**
     * Build a single time table by group
     *
     * @param ConfigurationGroup $group
     *
     * @return array
     */
    protected function buildSingleTimeTableByGroup(ConfigurationGroup $group)
    {
        $ids = [];
        foreach ($group->getConfigurations() as $configuration) {
            if ($configuration instanceof Configuration) {
                $ids[] = $configuration->getUid();
            }
        }
        return $this->timeTableService->getTimeTablesByConfigurationIds($ids);
    }
}
