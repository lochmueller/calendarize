<?php
/**
 * Recurrence service
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;

/**
 * Recurrence service
 *
 * @author Tim Lochmüller
 */
class RecurrenceService extends AbstractService
{

    /**
     * direction up
     */
    const DIRECTION_UP = 'up';

    /**
     * direction down
     */
    const DIRECTION_DOWN = 'down';

    /**
     * Get the date if the configuration of the next month
     *
     * @param \DateTime $date
     * @param string $recurrence
     * @param string $day
     *
     * @return \DateTime
     */
    public function getRecurrenceForNextMonth(\DateTime $date, $recurrence, $day)
    {
        return $this->getRecurrenceForCurrentMonth($date, $recurrence, $day, '+1 month');
    }

    /**
     * Get the date if the configuration of the next year
     *
     * @param \DateTime $date
     * @param string $recurrence
     * @param string $day
     *
     * @return \DateTime
     */
    public function getRecurrenceForNextYear(\DateTime $date, $recurrence, $day)
    {
        return $this->getRecurrenceForCurrentMonth($date, $recurrence, $day, '+1 year');
    }

    /**
     * Get the date if the configuration of the current month
     *
     * @param \DateTime $date
     * @param string $recurrence
     * @param string $day
     *
     * @return \DateTime|FALSE
     */
    protected function getRecurrenceForCurrentMonth(\DateTime $date, $recurrence, $day, $modify)
    {
        // clone and reset and move to next month
        $dateTime = clone $date;
        $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), 1);
        $dateTime->modify($modify);

        $days = $this->getValidDays($day);
        if (!$days) {
            return false;
        }

        switch ($recurrence) {
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
            default:
                return false;
        }
    }

    /**
     * Numbers are match against the date format 'N' 1 => mon till 7 => sun
     *
     * @param string $day
     *
     * @return array
     */
    protected function getValidDays($day)
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
                $days = range(1, 7);
                break;
            case ConfigurationInterface::DAY_SPECIAL_BUSINESS:
                $days = range(1, 6);
                break;
            case ConfigurationInterface::DAY_SPECIAL_WORKDAY:
                $days = range(1, 5);
                break;
            default:
                // no day
                break;
        }
        return $days;
    }

    /**
     * Find the modified in the current month
     *
     * @param \DateTime $dateTime
     * @param string $direction
     * @param array $validDays
     * @param int $position
     *
     * @return \DateTime|FALSE
     */
    protected function findDayInCurrentMonth($dateTime, $direction, $validDays, $position = 1)
    {
        if ($direction === self::DIRECTION_UP) {
            $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), 1);
            $modify = '+1 day';
        } else {
            $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), $dateTime->format('t'));
            $modify = '-1 day';
        }
        $validMonth = $dateTime->format('Y-m');
        while ($dateTime->format('Y-m') == $validMonth) {
            if (in_array($dateTime->format('N'), $validDays)) {
                $position--;
                if ($position === 0) {
                    return $dateTime;
                }
            }
            $dateTime->modify($modify);
        }
        return false;
    }

}