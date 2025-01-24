<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Domain\Repository\ConfigurationRepository;
use HDNET\Calendarize\Service\TimeTable\AbstractTimeTable;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Time table builder service.
 */
class TimeTableService extends AbstractService
{
    protected ?ConfigurationRepository $configurationRepository = null;

    public function setConfigurationRepository(ConfigurationRepository $configurationRepository): void
    {
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Build the timetable for the given configuration matrix (sorted).
     */
    public function getTimeTablesByConfigurationIds(array $ids, int $workspace): array
    {
        $timeTable = [];
        if (!$ids) {
            return $timeTable;
        }

        if (null === $this->configurationRepository) {
            $this->configurationRepository = GeneralUtility::makeInstance(ConfigurationRepository::class);
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
            // Do not use DI for repo to avoid problem in ext_localconf.php loading context
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
                    ContextualFeedbackSeverity::ERROR,
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
     */
    public function selectTimesBy(array $base, array $selectBy): array
    {
        $timeTableSelection = [];

        foreach ($base as $baseValue) {
            try {
                $eventStart = $this->getCompleteDate($baseValue, 'start');
                $eventEnd = $this->getCompleteDate($baseValue, 'end');
            } catch (\Exception $expression) {
                continue;
            }

            foreach ($selectBy as $selectByValue) {
                try {
                    $selectionStart = $this->getCompleteDate($selectByValue, 'start');
                    $selectionEnd = $this->getCompleteDate($selectByValue, 'end');
                } catch (\Exception $expression) {
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
     */
    public function checkAndRemoveTimes(array $base, array $remove): array
    {
        foreach ($base as $key => $value) {
            try {
                $eventStart = $this->getCompleteDate($value, 'start');
                $eventEnd = $this->getCompleteDate($value, 'end');
            } catch (\Exception $expression) {
                continue;
            }

            foreach ($remove as $removeValue) {
                try {
                    $removeStart = $this->getCompleteDate($removeValue, 'start');
                    $removeEnd = $this->getCompleteDate($removeValue, 'end');
                } catch (\Exception $expression) {
                    continue;
                }

                $startIn = ($eventStart >= $removeStart && $eventStart < $removeEnd);
                $endIn = ($eventEnd > $removeStart && $eventEnd <= $removeEnd);
                $envelope = ($eventStart < $removeStart && $eventEnd > $removeEnd);

                if ($startIn || $endIn || $envelope) {
                    unset($base[$key]);
                }
            }
        }

        return $base;
    }

    /**
     * Get the complete day.
     *
     * @throws \Exception
     */
    protected function getCompleteDate(array $record, string $position): \DateTime
    {
        if (!($record[$position . '_date'] instanceof \DateTimeInterface)) {
            throw new \Exception('no valid record', 1236781);
        }

        /** @var \DateTime $base */
        $base = clone $record[$position . '_date'];

        if (\is_int($record[$position . '_time']) && $record[$position . '_time'] > 0) {
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
