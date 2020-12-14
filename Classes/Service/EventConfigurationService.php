<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use Exception;
use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Ical\EventConfigurationInterface;
use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class EventConfigurationService.
 */
class EventConfigurationService extends AbstractService
{
    /**
     * Hydrates the calendarize configurations with the information from an imported event.
     * This adds or updates (depending on the importId) the configurations in the store.
     *
     * @param ObjectStorage<Configuration> $calendarize
     * @param EventConfigurationInterface  $event
     * @param string                       $importId
     * @param int                          $pid
     */
    public function hydrateCalendarize(
        ObjectStorage $calendarize,
        EventConfigurationInterface $event,
        string $importId,
        int $pid
    ): void {
        $configuration = $this->getOrCreateConfiguration($calendarize, $importId);

        $configuration->setImportId($importId);
        $configuration->setPid($pid);

        $configuration->setType(Configuration::TYPE_TIME);
        $configuration->setHandling(Configuration::HANDLING_INCLUDE);
        $configuration->setState($event->getState());
        $configuration->setAllDay($event->isAllDay());

        $configuration->setStartDate($event->getStartDate());
        $configuration->setEndDate($event->getEndDate());
        $configuration->setStartTime($event->getStartTime());
        $configuration->setEndTime($event->getEndTime());

        $this->hydrateRecurringConfiguration($configuration, $event->getRRule());
    }

    /**
     * Get existing configuration with the matching import id or creates a new configuration and adds it to the store.
     *
     * @param ObjectStorage<Configuration> $calendarize
     * @param string                       $importId
     *
     * @return Configuration
     */
    protected function getOrCreateConfiguration(ObjectStorage $calendarize, string $importId): Configuration
    {
        $configuration = false;
        // Get existing configuration if it matches the importId
        if (0 !== $calendarize->count()) {
            foreach ($calendarize as $item) {
                /** @var $item Configuration */
                if ($item->getImportId() === $importId) {
                    $configuration = $item;
                    break;
                }
            }
        }
        // Create configuration if not found / exists
        if (!$configuration) {
            $configuration = new Configuration();
            $configuration->setImportId($importId);
            $calendarize->attach($configuration);
        }

        return $configuration;
    }

    /**
     * Hydrates the configuration with recurrent configuration.
     * Invalid values are skipped.
     *
     * @param Configuration $configuration
     * @param array         $rrule
     */
    protected function hydrateRecurringConfiguration(Configuration $configuration, array $rrule): void
    {
        foreach ($rrule as $key => $value) {
            switch (strtoupper($key)) {
                case 'FREQ':
                    // The spelling of the frequency in RFC 5545 matches the constants in ConfigurationInterface.
                    // Currently only a subset of frequencies is implemented (missing "SECONDLY" / "MINUTELY" / "HOURLY")
                    $configuration->setFrequency(strtolower($value));
                    break;
                case 'UNTIL':
                    try {
                        $tillDate = new \DateTime($value);
                    } catch (Exception $e) {
                        break;
                    }
                    if ($tillDate <= $configuration->getStartDate()) {
                        break;
                    }

                    $configuration->setTillDate(DateTimeUtility::getDayStart($tillDate));
                    break;
                case 'INTERVAL':
                    $interval = (int)$value;
                    if ($interval < 1) {
                        break;
                    }

                    $configuration->setCounterInterval($interval);
                    break;
                case 'COUNT':
                    // RFC-5545 3.3.10:
                    // The COUNT rule part defines the number of occurrences at which to range-bound the recurrence.
                    // The "DTSTART" property value always counts as the first occurrence.
                    $count = (int)$value;
                    if ($count < 1) {
                        break;
                    }

                    // Since calendarize does not count the DTSTART as first occurrence, the count is decremented by one.
                    $configuration->setCounterAmount($count - 1);
                    break;
            }
        }
    }
}
