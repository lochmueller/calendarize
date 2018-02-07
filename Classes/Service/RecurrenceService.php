<?php

/**
 * Recurrence service.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;

/**
 * Recurrence service.
 */
class RecurrenceService extends AbstractService
{
    /**
     * direction up.
     */
    const DIRECTION_UP = 'up';

    /**
     * direction down.
     */
    const DIRECTION_DOWN = 'down';

    /**
     * Get the date if the configuration of the next month.
     *
     * @param \DateTime $date
     * @param string    $recurrence
     * @param string    $day
     *
     * @return \DateTime
     */
    public function getRecurrenceForNextMonth(\DateTime $date, string $recurrence, string $day)
    {
        return $this->getRecurrenceForCurrentMonth($date, $recurrence, $day, '+1 month');
    }

    /**
     * Get the date if the configuration of the next year.
     *
     * @param \DateTime $date
     * @param string    $recurrence
     * @param string    $day
     *
     * @return \DateTime
     */
    public function getRecurrenceForNextYear(\DateTime $date, string $recurrence, string $day)
    {
        return $this->getRecurrenceForCurrentMonth($date, $recurrence, $day, '+1 year');
    }

    /**
     * Get the date if the configuration of the current month.
     *
     * @param \DateTime $date
     * @param string    $recurrence
     * @param string    $day
     * @param string    $modify
     *
     * @return \DateTime|false
     */
    protected function getRecurrenceForCurrentMonth(\DateTime $date, string $recurrence, string $day, string $modify)
    {
        // clone and reset and move to next month
        $dateTime = clone $date;
        $dateTime->setDate((int) $dateTime->format('Y'), (int) $dateTime->format('m'), 1);
        $dateTime->modify($modify);

        $days = $this->getValidDays($day);
        if (empty($days)) {
            return false;
        }

        switch ($recurrence) {
            case ConfigurationInterface::RECURRENCE_THIRD_LAST:
                return $this->findDayInCurrentMonth($dateTime, self::DIRECTION_DOWN, $days, 3);
            case ConfigurationInterface::RECURRENCE_NEXT_TO_LAST:
                return $this->findDayInCurrentMonth($dateTime, self::DIRECTION_DOWN, $days, 2);
            case ConfigurationInterface::RECURRENCE_LAST:
                return $this->findDayInCurrentMonth($dateTime, self::DIRECTION_DOWN, $days);
            case ConfigurationInterface::RECURRENCE_FIRST:
                return $this->findDayInCurrentMonth($dateTime, self::DIRECTION_UP, $days);
            case ConfigurationInterface::RECURRENCE_SECOND:
                return $this->findDayInCurrentMonth($dateTime, self::DIRECTION_UP, $days, 2);
            case ConfigurationInterface::RECURRENCE_THIRD:
                return $this->findDayInCurrentMonth($dateTime, self::DIRECTION_UP, $days, 3);
            case ConfigurationInterface::RECURRENCE_FOURTH:
                return $this->findDayInCurrentMonth($dateTime, self::DIRECTION_UP, $days, 4);
            case ConfigurationInterface::RECURRENCE_FIFTH:
                return $this->findDayInCurrentMonth($dateTime, self::DIRECTION_UP, $days, 5);
            default:
                return false;
        }
    }

    /**
     * Numbers are match against the date format 'N' 1 => mon till 7 => sun.
     *
     * @param string $day
     *
     * @return array
     */
    protected function getValidDays(string $day): array
    {
        $days = [];
        switch ($day) {
            case ConfigurationInterface::DAY_MONDAY:
                $days[] = 1;
                break;
            case ConfigurationInterface::DAY_TUESDAY:
                $days[] = 2;
                break;
            case ConfigurationInterface::DAY_WEDNESDAY:
                $days[] = 3;
                break;
            case ConfigurationInterface::DAY_THURSDAY:
                $days[] = 4;
                break;
            case ConfigurationInterface::DAY_FRIDAY:
                $days[] = 5;
                break;
            case ConfigurationInterface::DAY_SATURDAY:
                $days[] = 6;
                break;
            case ConfigurationInterface::DAY_SUNDAY:
                $days[] = 7;
                break;
            case ConfigurationInterface::DAY_SPECIAL_WEEKEND:
                $days[] = 7;
                $days[] = 6;
                break;
            case ConfigurationInterface::DAY_SPECIAL_WEEKDAY:
                $days = \range(1, 7);
                break;
            case ConfigurationInterface::DAY_SPECIAL_BUSINESS:
                $days = \range(1, 6);
                break;
            case ConfigurationInterface::DAY_SPECIAL_WORKDAY:
                $days = \range(1, 5);
                break;
            default:
                // no day
                break;
        }

        return $days;
    }

    /**
     * Find the modified in the current month.
     *
     * @param \DateTime $dateTime
     * @param string    $direction
     * @param array     $validDays
     * @param int       $position
     *
     * @return \DateTime|false
     */
    protected function findDayInCurrentMonth(\DateTime $dateTime, string $direction, array $validDays, int $position = 1)
    {
        if (self::DIRECTION_UP === $direction) {
            $dateTime->setDate((int) $dateTime->format('Y'), (int) $dateTime->format('m'), 1);
            $modify = '+1 day';
        } else {
            $dateTime->setDate((int) $dateTime->format('Y'), (int) $dateTime->format('m'), (int) $dateTime->format('t'));
            $modify = '-1 day';
        }
        $validMonth = $dateTime->format('Y-m');
        while ($dateTime->format('Y-m') === $validMonth) {
            if (\in_array((int) $dateTime->format('N'), $validDays, true)) {
                --$position;
                if (0 === $position) {
                    return $dateTime;
                }
            }
            $dateTime->modify($modify);
        }

        return false;
    }
}
