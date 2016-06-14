<?php
/**
 * DateTime Utility
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Utility;

use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * DateTime Utility
 *
 * @author Tim Lochmüller
 */
class DateTimeUtility
{

    /**
     * One second
     */
    const SECONDS_SECOND = 1;

    /**
     * One minute in seconds
     */
    const SECONDS_MINUTE = 60;

    /**
     * One hour in seconds
     */
    const SECONDS_HOUR = 3600;

    /**
     * One day in seconds
     */
    const SECONDS_DAY = 86400;

    /**
     * One week in seconds
     */
    const SECONDS_WEEK = 604800;

    /**
     * One year in seconds (365 days)
     */
    const SECONDS_YEAR = 31536000;

    /**
     * One decade in seconds (base on a 365 days year)
     */
    const SECONDS_DECADE = 315360000;

    /**
     * Convert a Week/Year combination to a DateTime of the first day of week
     *
     * @param int $week
     * @param int $year
     *
     * @return \DateTime
     */
    public static function convertWeekYear2DayMonthYear($week, $year)
    {
        return self::normalizeDateTimeSingle(strtotime($year . 'W' . $week . '1'));
    }

    /**
     * Time zone is set by the TYPO3 core
     *
     * @return \DateTimeZone
     * @see \TYPO3\CMS\Core\Core\Bootstrap->setDefaultTimezone()
     */
    public static function getTimeZone()
    {
        return new \DateTimeZone(date_default_timezone_get());
    }

    /**
     * Get the time seconds of the given date (TYPO3 Backend style)
     *
     * @param \DateTime $dateTime
     *
     * @return int
     */
    public static function getDaySecondsOfDateTime(\DateTime $dateTime)
    {
        $hours = (int)$dateTime->format('G');
        $minutes = $hours * 60 + (int)$dateTime->format('i');
        return $minutes * 60 + (int)$dateTime->format('s');
    }

    /**
     * Get a normalize date time object
     *
     * @param int|null $day
     * @param int|null $month
     * @param int|null $year
     *
     * @return \DateTime
     */
    public static function normalizeDateTime($day = null, $month = null, $year = null)
    {
        $date = self::getNow();
        if (!MathUtility::canBeInterpretedAsInteger($year)) {
            $year = $date->format('Y');
        }
        if (!MathUtility::canBeInterpretedAsInteger($month)) {
            $month = $date->format('m');
        }
        if (!MathUtility::canBeInterpretedAsInteger($day)) {
            $day = $date->format('d');
        }
        $date->setDate($year, $month, $day);
        $date->setTime(0, 0, 0);
        if ($date->format('m') > $month) {
            $date->modify('last day of last month');
        } elseif ($date->format('m') < $month) {
            $date->modify('first day of next month');
        }
        return $date;
    }

    /**
     * Reset the DateTime
     *
     * @param \DateTime $dateTime
     *
     * @return \DateTime
     */
    public static function resetTime($dateTime = null)
    {
        $dateTime = self::normalizeDateTimeSingle($dateTime);
        $dateTime->setTime(0, 0, 0);
        return $dateTime;
    }

    /**
     * Get a normalize date time object
     *
     * @param int|null|string|\DateTime $dateInformation
     *
     * @return \DateTime
     */
    public static function normalizeDateTimeSingle($dateInformation)
    {
        if ($dateInformation instanceof \DateTime) {
            return $dateInformation;
        } elseif (MathUtility::canBeInterpretedAsInteger($dateInformation)) {
            $dateInformation = '@' . $dateInformation;
        } elseif (!is_string($dateInformation)) {
            return self::getNow();
        }
        return new \DateTime($dateInformation, DateTimeUtility::getTimeZone());
    }

    /**
     * Get the current Date (normalized optimized for queries, because SIM_ACCESS_TIME is rounded to minutes)
     *
     * @return \DateTime
     */
    public static function getNow()
    {
        return self::normalizeDateTimeSingle((int)$GLOBALS['SIM_ACCESS_TIME']);
    }
}
