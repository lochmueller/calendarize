<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationGroup;
use HDNET\Calendarize\Service\AbstractService;
use HDNET\Calendarize\Service\TimeTableService;

/**
 * Abstract time table service.
 */
abstract class AbstractTimeTable extends AbstractService
{
    /**
     * Timetable service.
     */
    protected TimeTableService $timeTableService;

    /**
     * Inject timetable service.
     */
    public function injectTimeTableService(TimeTableService $timeTableService): void
    {
        $this->timeTableService = $timeTableService;
    }

    /**
     * Modify the given times via the configuration.
     */
    abstract public function handleConfiguration(array &$times, Configuration $configuration): void;

    /**
     * Build a single timetable by group.
     */
    protected function buildSingleTimeTableByGroup(ConfigurationGroup $group): array
    {
        return $this->timeTableService->getTimeTablesByConfigurationIds($group->getConfigurationIds(), 0);
    }

    /**
     * Calculate a hash for the key of the given entry.
     * This prevents double entries in the index.
     */
    protected function calculateEntryKey(array $entry): string
    {
        // crc32 may be faster but have more collision-potential
        return hash('md5', json_encode($entry));
    }
}
