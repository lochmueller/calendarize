<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Ical\EventConfigurationInterface;
use HDNET\Calendarize\Utility\DateTimeUtility;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class EventConfigurationService.
 */
class EventConfigurationService extends AbstractService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const DAY_MAPPING = [
        'SU' => ConfigurationInterface::DAY_SUNDAY,
        'MO' => ConfigurationInterface::DAY_MONDAY,
        'TU' => ConfigurationInterface::DAY_TUESDAY,
        'WE' => ConfigurationInterface::DAY_WEDNESDAY,
        'TH' => ConfigurationInterface::DAY_THURSDAY,
        'FR' => ConfigurationInterface::DAY_FRIDAY,
        'SA' => ConfigurationInterface::DAY_SATURDAY,
    ];
    public const RECURRENCE_MAPPING = [
        -3 => ConfigurationInterface::RECURRENCE_THIRD_LAST,
        -2 => ConfigurationInterface::RECURRENCE_NEXT_TO_LAST,
        -1 => ConfigurationInterface::RECURRENCE_LAST,
        1 => ConfigurationInterface::RECURRENCE_FIRST,
        2 => ConfigurationInterface::RECURRENCE_SECOND,
        3 => ConfigurationInterface::RECURRENCE_THIRD,
        4 => ConfigurationInterface::RECURRENCE_FOURTH,
        5 => ConfigurationInterface::RECURRENCE_FIFTH,
        6 => ConfigurationInterface::RECURRENCE_LAST,
    ];

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

        $this->hydrateRecurringConfiguration($configuration, $event->getRRule(), $importId);
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
    protected function hydrateRecurringConfiguration(Configuration $configuration, array $rrule, string $importId = ''): void
    {
        $row = $this->mapRruleToConfiguration($rrule, $importId);

        // Get default values, so that all values are "reset" and independent of the previous import
        $defaults = (new \ReflectionClass(Configuration::class))->getDefaultProperties();

        $configuration->setFrequency($row['frequency'] ?? $defaults['frequency']);
        $configuration->setTillDate($row['tillDate'] ?? $defaults['tillDate']);
        $configuration->setRecurrence($row['recurrence'] ?? $defaults['recurrence']);
        $configuration->setCounterAmount($row['counterAmount'] ?? $defaults['counterAmount']);
        $configuration->setCounterInterval($row['counterInterval'] ?? $defaults['counterInterval']);
        $configuration->setDay($row['day'] ?? $defaults['day']);
    }

    protected function mapRruleToConfiguration(array $rrule, string $importId = ''): array
    {
        $row = [];

        // FREQ (required)
        $frequency = strtolower($rrule['FREQ'] ?? '');
        // The spelling of the frequency in RFC 5545 matches the constants in ConfigurationInterface.
        // Currently, only a subset of frequencies are implemented (missing "SECONDLY")
        if (!\in_array($frequency, ConfigurationInterface::VALID_FREQUENCIES)) {
            if (!empty($frequency)) {
                // Only log malformed frequencies and prevent spamming of normal events without RRULE
                $this->logger->warning('Invalid frequency="{frequency}" in RRULE.', [
                    'frequency' => $frequency,
                    'importId' => $importId,
                ]);
            }
            $row['frequency'] = $frequency = ConfigurationInterface::FREQUENCY_NONE;

            return $row;
        }
        $row['frequency'] = $frequency;

        // UNTIL (optional)
        if ($rrule['UNTIL'] ?? false) {
            // The UNTIL rule part defines a DATE or DATE-TIME value that bounds
            // the recurrence rule in an inclusive manner.
            try {
                $until = new \DateTime($rrule['UNTIL']);
                $row['tillDate'] = DateTimeUtility::getDayStart($until);
            } catch (\Exception $e) {
                $this->logger->warning('Invalid UNTIL="{until}" date in RRULE.', [
                    'until' => $rrule['UNTIL'],
                    'importId' => $importId,
                    'exception' => $e,
                ]);
            }
        } elseif (MathUtility::canBeInterpretedAsInteger($rrule['COUNT'] ?? '')) {
            // COUNT (optional)
            // The COUNT rule part defines the number of occurrences at which to range-bound the recurrence.
            // The "DTSTART" property value always counts as the first occurrence.
            $count = (int)$rrule['COUNT'];
            if ($count > 1) {
                // Since calendarize does not count the DTSTART as first occurrence, the count is decremented by one.
                // COUNT == 1 --> counterAmount == 0 is not supported, since 0 is used as not set value
                $row['counterAmount'] = $count - 1;
            } else {
                $this->logger->warning('Invalid COUNT={count} in RRULE.', [
                    'count' => $count,
                    'importId' => $importId,
                ]);
            }
        }

        // INTERVAL (OPTIONAL)
        if (MathUtility::canBeInterpretedAsInteger($rrule['INTERVAL'] ?? '')) {
            // The INTERVAL rule part contains a positive integer representing at
            // which intervals the recurrence rule repeats. The default value is "1"
            $interval = (int)$rrule['INTERVAL'];
            if ($interval > 0) {
                $row['counterInterval'] = $interval;
            } else {
                $this->logger->warning('Invalid INTERVAL={interval} in RRULE.', [
                    'interval' => $interval,
                    'importId' => $importId,
                ]);
            }
        }

        // BYDAY (OPTIONAL)
        if (
            ($rrule['BYDAY'] ?? false)
            && \in_array($frequency, [ConfigurationInterface::FREQUENCY_MONTHLY, ConfigurationInterface::FREQUENCY_YEARLY], true)
        ) {
            if (\is_array($rrule['BYDAY'])) {
                $byDay = $rrule['BYDAY'];
            } else {
                // Split e.g. BYDAY=TU,TH
                $byDay = GeneralUtility::trimExplode(',', $rrule['BYDAY'], true);
            }
            $days = [];
            $nthDays = [];
            foreach ($byDay as $dayPart) {
                // Splits -1SO into the day of the week and the numeric value (offset)
                $isValid = preg_match('/^([+-]?\d+)?(MO|TU|WE|TH|FR|SA|SU)$/', $dayPart, $matches);
                if (!$isValid) {
                    $this->logger->warning('Invalid BYDAY={day} in RRULE.', [
                        'day' => $dayPart,
                        'importId' => $importId,
                    ]);
                    continue;
                }
                $days[] = self::DAY_MAPPING[$matches[2]];
                // Store the numeric value
                if ($matches[1]) {
                    $nthDays[] = (int)$matches[1];
                }
            }
            $row['day'] = implode(',', $days);

            // The numeric values corresponds to an offset in the month (e.g. first/last)
            // Only valid for monthly/yearly frequencies
            if (1 === \count($nthDays)) {
                // Currently, recurrence is only supported for one day of the week
                // Other construct are not supported
                $recurrence = self::RECURRENCE_MAPPING[$nthDays[0]] ?? false;
                if (false !== $recurrence) {
                    $row['recurrence'] = $recurrence;
                } else {
                    $this->logger->warning('Invalid recurrence {recurrence} in BYDAY RRULE.', [
                        'recurrence' => $nthDays[0],
                        'importId' => $importId,
                    ]);
                }
            } elseif (!empty($nthDays)) {
                $this->logger->warning('Multiple recurrence {nthDays} in BYDAY RRULE are not supported.', [
                    'nthDays' => $nthDays,
                    'importId' => $importId,
                ]);
            }

            // BYSETPOS can be a comma separated list, however this only supports one value
            if (MathUtility::canBeInterpretedAsInteger($rrule['BYSETPOS'] ?? '')) {
                // only relevant for monthly/yearly frequencies with BYDAY set
                $setPos = (int)$rrule['BYSETPOS'];

                if (isset(self::RECURRENCE_MAPPING[$setPos])) {
                    $row['recurrence'] = self::RECURRENCE_MAPPING[$setPos];
                } else {
                    $this->logger->warning('Invalid BYSETPOS={setPos} in RRULE.', [
                        'setPos' => $rrule['BYSETPOS'],
                        'importId' => $importId,
                    ]);
                }
            }
        }

        return $row;
    }
}
