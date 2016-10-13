<?php
/**
 * Abstract time table service
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationGroup;
use HDNET\Calendarize\Exception;
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

    /**
     * Remove excluded events
     *
     * @param $base
     * @param $remove
     *
     * @return mixed
     */
    protected function checkAndRemoveTimes($base, $remove)
    {
        foreach ($base as $key => $value) {
            foreach ($remove as $removeValue) {
                try {
                    $eventStart = $this->getCompleteDate($value, 'start');
                    $eventEnd = $this->getCompleteDate($value, 'end');
                    $removeStart = $this->getCompleteDate($removeValue, 'start');
                    $removeEnd = $this->getCompleteDate($removeValue, 'end');
                } catch (Exception $ex) {
                    continue;
                }

                $startIn = ($eventStart >= $removeStart && $eventStart < $removeEnd);
                $endIn = ($eventEnd > $removeStart && $eventEnd <= $removeEnd);
                $envelope = ($eventStart < $removeStart && $eventEnd > $removeEnd);

                if ($startIn || $endIn || $envelope) {
                    unset($base[$key]);
                    continue;
                }
            }
        }

        return $base;
    }

    /**
     * Get the complete day
     *
     * @param array  $record
     * @param string $position
     *
     * @return \DateTime
     * @throws Exception
     */
    protected function getCompleteDate(array $record, $position)
    {
        if (!($record[$position . '_date'] instanceof \DateTime)) {
            throw new Exception('no valid record', 1236781);
        }
        /** @var \DateTime $base */
        $base = clone $record[$position . '_date'];
        if (is_int($record[$position . '_time'])) {
            $base->setTime(0, 0, 0);
            $base->modify('+ ' . $record[$position . '_time'] . ' seconds');
        }
        return $base;
    }
}
