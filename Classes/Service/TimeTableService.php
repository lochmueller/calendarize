<?php

/**
 * Time table builder service.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Domain\Repository\ConfigurationRepository;
use HDNET\Calendarize\Service\TimeTable\AbstractTimeTable;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Time table builder service.
 */
class TimeTableService extends AbstractService
{
    /**
     * @var ConfigurationRepository
     */
    protected $configurationRepository;

    /**
     * @param ConfigurationRepository $configurationRepository
     */
    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Build the timetable for the given configuration matrix (sorted).
     *
     * @param array $ids
     * @param int   $workspace
     *
     * @return array
     */
    public function getTimeTablesByConfigurationIds(array $ids, int $workspace)
    {
        $timeTable = [];
        if (!$ids) {
            return $timeTable;
        }

        foreach ($ids as $configurationUid) {
            if ($workspace) {
                $row = BackendUtility::getRecord('tx_calendarize_domain_model_configuration', $configurationUid);
                BackendUtility::workspaceOL('tx_calendarize_domain_model_configuration', $row, $workspace);
                if (isset($row['_ORIG_uid'])) {
                    $configurationUid = (int)$row['_ORIG_uid'];
                }
            }

            // Disable Workspace for selection to get also offline versions of configuration
            $GLOBALS['TCA']['tx_calendarize_domain_model_configuration']['ctrl']['versioningWS'] = false;
            $configuration = $this->configurationRepository->findByUid($configurationUid);
            $GLOBALS['TCA']['tx_calendarize_domain_model_configuration']['ctrl']['versioningWS'] = true;
            if (!($configuration instanceof Configuration)) {
                continue;
            }

            try {
                $handler = $this->buildConfigurationHandler($configuration);
            } catch (\Exception $exception) {
                HelperUtility::createFlashMessage(
                    $exception->getMessage(),
                    'Index invalid',
                    FlashMessage::ERROR
                );
                continue;
            }

            if (ConfigurationInterface::HANDLING_INCLUDE === $configuration->getHandling()) {
                $handler->handleConfiguration($timeTable, $configuration);
            } elseif (ConfigurationInterface::HANDLING_EXCLUDE === $configuration->getHandling()) {
                $timesToExclude = [];
                $handler->handleConfiguration($timesToExclude, $configuration);
                $timeTable = $this->checkAndRemoveTimes($timeTable, $timesToExclude);
            } elseif (ConfigurationInterface::HANDLING_OVERRIDE === $configuration->getHandling()) {
                // first remove overridden times
                $timesToOverride = [];
                $handler->handleConfiguration($timesToOverride, $configuration);
                $timeTable = $this->checkAndRemoveTimes($timeTable, $timesToOverride);
                // then add new times
                $handler->handleConfiguration($timeTable, $configuration);
            } elseif (ConfigurationInterface::HANDLING_CUTOUT === $configuration->getHandling()) {
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
            } catch (\Exception $ex) {
                continue;
            }

            foreach ($selectBy as $selectByValue) {
                try {
                    $selectionStart = $this->getCompleteDate($selectByValue, 'start');
                    $selectionEnd = $this->getCompleteDate($selectByValue, 'end');
                } catch (\Exception $ex) {
                    continue;
                }

                $startIn = ($eventStart >= $selectionStart && $eventStart < $selectionEnd);
                $endIn = ($eventEnd > $selectionStart && $eventEnd <= $selectionEnd);
                $envelope = ($eventStart < $selectionStart && $eventEnd > $selectionEnd);

                if (($startIn && $endIn) || $envelope) {
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
            } catch (\Exception $ex) {
                continue;
            }

            foreach ($remove as $removeValue) {
                try {
                    $removeStart = $this->getCompleteDate($removeValue, 'start');
                    $removeEnd = $this->getCompleteDate($removeValue, 'end');
                } catch (\Exception $ex) {
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
     * @throws \Exception
     */
    protected function getCompleteDate(array $record, $position)
    {
        if (!($record[$position . '_date'] instanceof \DateTimeInterface)) {
            throw new \Exception('no valid record', 1236781);
        }
        /** @var \DateTime $base */
        $base = clone $record[$position . '_date'];
        if (\is_int($record[$position . '_time']) && (int)$record[$position . '_time'] > 0) {
            // Fix handling, if the time field contains a complete timestamp
            $seconds = $record[$position . '_time'] % DateTimeUtility::SECONDS_DAY;
            $base->setTime(0, 0, 0);
            $base->modify('+ ' . $seconds . ' seconds');
        }
        if ($record['all_day'] && 'end' === $position) {
            $base->setTime(0, 0, 0);
            $base->modify('+1 day');
        }

        return $base;
    }

    /**
     * Build the configuration handler.
     *
     * @param Configuration $configuration
     *
     * @return AbstractTimeTable
     *
     * @throws \Exception
     */
    protected function buildConfigurationHandler(Configuration $configuration): AbstractTimeTable
    {
        $handler = 'HDNET\\Calendarize\\Service\\TimeTable\\' . ucfirst($configuration->getType()) . 'TimeTable';
        if (class_exists($handler)) {
            return GeneralUtility::makeInstance($handler);
        }

        /** @var SecondaryTimeTableService $secondaryTimeTableService */
        $secondaryTimeTableService = GeneralUtility::makeInstance(SecondaryTimeTableService::class);
        $services = $secondaryTimeTableService->getSecondaryTimeTables();
        foreach ($services as $service) {
            if ($service->getIdentifier() === $configuration->getType()) {
                return $service;
            }
        }

        throw new \Exception('There is no TimeTable handler for the given configuration type: ' . $configuration->getType(), 1236781);
    }
}
