<?php
/**
 * Time table builder service.
 */
namespace HDNET\Calendarize\Service;

use Exception;
use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Domain\Repository\ConfigurationRepository;
use HDNET\Calendarize\Service\TimeTable\AbstractTimeTable;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * Time table builder service.
 */
class TimeTableService extends AbstractService
{
    /**
     * Build the timetable for the given configuration matrix (sorted).
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
            } elseif ($configuration->getHandling() === ConfigurationInterface::HANDLING_EXCLUDE) {
                $timesToExclude = [];
                $handler->handleConfiguration($timesToExclude, $configuration);
                $timeTable = $this->checkAndRemoveTimes($timeTable, $timesToExclude);
            } elseif ($configuration->getHandling() === ConfigurationInterface::HANDLING_OVERRIDE) {
                // first remove overridden times
                $timesToOverride = [];
                $handler->handleConfiguration($timesToOverride, $configuration);
                $timeTable = $this->checkAndRemoveTimes($timeTable, $timesToOverride);
                // then add new times
                $handler->handleConfiguration($timeTable, $configuration);
            } elseif ($configuration->getHandling() === ConfigurationInterface::HANDLING_CUTOUT) {
                $timesToSelectBy = [];
                $handler->handleConfiguration($timesToSelectBy, $configuration);
                $timeTable = $this->selectTimesBy($timeTable, $timesToSelectBy);
            }
        }

        return $timeTable;
    }

    /**
     * Selects events by given times.
     *
     * @param array $base
     * @param array $selectBy
     *
     * @return array
     */
    public function selectTimesBy($base, $selectBy)
    {
        $timeTableSelection = [];

        foreach ($base as $baseValue) {
            try {
                $eventStart = $this->getCompleteDate($baseValue, 'start');
                $eventEnd = $this->getCompleteDate($baseValue, 'end');
            } catch (Exception $ex) {
                continue;
            }

            foreach ($selectBy as $selectByValue) {
                try {
                    $selectionStart = $this->getCompleteDate($selectByValue, 'start');
                    $selectionEnd = $this->getCompleteDate($selectByValue, 'end');
                } catch (Exception $ex) {
                    continue;
                }

                $startIn = ($eventStart >= $selectionStart && $eventStart < $selectionEnd);
                $endIn = ($eventEnd > $selectionStart && $eventEnd <= $selectionEnd);
                $envelope = ($eventStart < $selectionStart && $eventEnd > $selectionEnd);

                if ($startIn && $endIn || $envelope) {
                    $timeTableSelection[] = $baseValue;
                    break;
                }
            }
        }

        return $timeTableSelection;
    }

    /**
     * Remove excluded events.
     *
     * @param array $base
     * @param $remove
     *
     * @return array
     */
    public function checkAndRemoveTimes($base, $remove)
    {
        foreach ($base as $key => $value) {
            try {
                $eventStart = $this->getCompleteDate($value, 'start');
                $eventEnd = $this->getCompleteDate($value, 'end');
            } catch (Exception $ex) {
                continue;
            }

            foreach ($remove as $removeValue) {
                try {
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
     * Get the complete day.
     *
     * @param array  $record
     * @param string $position
     *
     * @return \DateTime
     *
     * @throws Exception
     */
    protected function getCompleteDate(array $record, $position)
    {
        if (!($record[$position . '_date'] instanceof \DateTimeInterface)) {
            throw new Exception('no valid record', 1236781);
        }
        /** @var \DateTime $base */
        $base = clone $record[$position . '_date'];
        if (is_int($record[$position . '_time']) && (int) $record[$position . '_time'] > 0) {
            // Fix handling, if the time field contains a complete timestamp
            $seconds = $record[$position . '_time'] % DateTimeUtility::SECONDS_DAY;
            $base->setTime(0, 0, 0);
            $base->modify('+ ' . $seconds . ' seconds');
        }

        return $base;
    }

    /**
     * Build the configuration handler.
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
