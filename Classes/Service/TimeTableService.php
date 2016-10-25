<?php
/**
 * Time table builder service
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use Exception;
use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Domain\Repository\ConfigurationRepository;
use HDNET\Calendarize\Service\TimeTable\AbstractTimeTable;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * Time table builder service
 *
 * @author Tim Lochmüller
 */
class TimeTableService extends AbstractService
{

    /**
     * Build the timetable for the given configuration matrix (sorted)
     *
     * @param array $ids
     *
     * @return array
     */
    public function getTimeTablesByConfigurationIds(array $ids)
    {
        $timeTable = [];
        if (!$ids) {
            return $timeTable;
        }

        /** @var ConfigurationRepository $configRepository */
        $configRepository = HelperUtility::create(ConfigurationRepository::class);
        foreach ($ids as $configurationUid) {
            $configuration = $configRepository->findByUid($configurationUid);
            if (!($configuration instanceof Configuration)) {
                continue;
            }

            $handler = $this->buildConfigurationHandler($configuration);
            if (!$handler) {
                HelperUtility::createFlashMessage(
                    'There is no TimeTable handler for the given configuration type: ' . $configuration->getType(),
                    'Index invalid',
                    FlashMessage::ERROR
                );
                continue;
            }

            if ($configuration->getHandling() === ConfigurationInterface::HANDLING_INCLUDE) {
                $handler->handleConfiguration($timeTable, $configuration);
            } elseif ($configuration->getHandling() === ConfigurationInterface::HANDLING_INCLUDE) {
                $localTimes = [];
                $handler->handleConfiguration($localTimes, $configuration);
                $timeTable = $this->checkAndRemoveTimes($timeTable, $localTimes);
            } elseif ($configuration->getHandling() === ConfigurationInterface::HANDLING_OVERRIDE) {
                $localTimes = [];
                $handler->handleConfiguration($localTimes, $configuration);
                $timeTable = $this->checkAndRemoveTimes($timeTable, $localTimes);
                $handler->handleConfiguration($timeTable, $configuration);
            }
        }

        return $timeTable;
    }


    /**
     * Remove excluded events
     *
     * @param array $base
     * @param $remove
     *
     * @return array
     */
    public function checkAndRemoveTimes($base, $remove)
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
     * @param array $record
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

    /**
     * Build the configuration handler
     *
     * @param Configuration $configuration
     *
     * @return bool|AbstractTimeTable
     */
    protected function buildConfigurationHandler(Configuration $configuration)
    {
        $handler = 'HDNET\\Calendarize\\Service\\TimeTable\\' . ucfirst($configuration->getType()) . 'TimeTable';
        if (!class_exists($handler)) {
            return false;
        }
        return HelperUtility::create($handler);
    }
}
