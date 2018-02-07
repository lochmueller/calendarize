<?php

/**
 * DateTime Utility.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * DateTime Utility.
 */
class DateTimeUtility
{
    /**
     * One second.
     */
    const SECONDS_SECOND = 1;

    /**
     * One minute in seconds.
     */
    const SECONDS_MINUTE = 60;

    /**
     * One hour in seconds.
     */
    const SECONDS_HOUR = 3600;

    /**
     * One day in seconds.
     */
    const SECONDS_DAY = 86400;

    /**
     * One week in seconds.
     */
    const SECONDS_WEEK = 604800;

    /**
     * One year in seconds (365 days).
     */
    const SECONDS_YEAR = 31536000;

    /**
     * One decade in seconds (base on a 365 days year).
     */
    const SECONDS_DECADE = 315360000;

    /**
     * Format date (Backend).
     */
    const FORMAT_DATE_BACKEND = '%a %d.%m.%Y';

    /**
     * Convert a Week/Year combination to a DateTime of the first day of week.
     *
     * @param int $week
     * @param int $year
     * @param int $startDay
     *
     * @return \DateTime
     */
    public static function convertWeekYear2DayMonthYear($week, $year, $startDay = 1): \DateTime
    {
        $week = \str_pad((string) $week, 2, '0', STR_PAD_LEFT);

        return self::normalizeDateTimeSingle(\strtotime($year . '-W' . $week . '-' . $startDay));
    }

    /**
     * Time zone is set by the TYPO3 core.
     *
     * @return \DateTimeZone
     *
     * @see \TYPO3\CMS\Core\Core\Bootstrap->setDefaultTimezone()
     */
    public static function getTimeZone()
    {
        return new \DateTimeZone(\date_default_timezone_get());
    }

    /**
     * Get the time seconds of the given date (TYPO3 Backend style).
     *
     * @param \DateTime $dateTime
     *
     * @return int
     */
    public static function getDaySecondsOfDateTime(\DateTime $dateTime): int
    {
        $hours = (int) $dateTime->format('G');
        $minutes = $hours * self::SECONDS_MINUTE + (int) $dateTime->format('i');

        return $minutes * self::SECONDS_MINUTE + (int) $dateTime->format('s');
    }

    /**
     * Get a normalize date time object.
     *
     * @param int|null $day
     * @param int|null $month
     * @param int|null $year
     *
     * @return \DateTime
     */
    public static function normalizeDateTime($day = null, $month = null, $year = null): \DateTime
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
        $date->setDate((int) $year, (int) $month, (int) $day);
        $date->setTime(0, 0, 0);
        if ($date->format('m') > $month) {
            $date->modify('last day of last month');
        } elseif ($date->format('m') < $month) {
            $date->modify('first day of next month');
        }

        return $date;
    }

    /**
     * Reset the DateTime.
     *
     * @param \DateTime $dateTime
     *
     * @return \DateTime
     */
    public static function resetTime($dateTime = null): \DateTime
    {
        $dateTime = self::normalizeDateTimeSingle($dateTime);
        $dateTime->setTime(0, 0, 0);

        return $dateTime;
    }

    /**
     * Get a normalized date time object. The timezone of the returned object is
     * UTC for integer parameters and server timezone for everything else.
     *
     * @param int|null|string|\DateTime $dateInformation
     *
     * @return \DateTime
     */
    public static function normalizeDateTimeSingle($dateInformation): \DateTime
    {
        if ($dateInformation instanceof \DateTimeInterface) {
            return $dateInformation;
        }
        if (MathUtility::canBeInterpretedAsInteger($dateInformation)) {
            // http://php.net/manual/en/datetime.construct#refsect1-datetime.construct-parameters :
            // The $timezone parameter and the current timezone are ignored [ie. set to UTC] when the $time parameter [...] is a UNIX timestamp (e.g. @946684800) [...]
            return new \DateTime("@$dateInformation");
        }
        if (\is_string($dateInformation)) {
            return new \DateTime($dateInformation);
        }

        return self::getNow();
    }

    /**
     * Get the current date (normalized optimized for queries, because SIM_ACCESS_TIME is rounded to minutes)
     * in the current timezone.
     *
     * @return \DateTime
     */
    public static function getNow(): \DateTime
    {
        // NOTE that new \DateTime('@timestamp') does NOT work - @see comment in normalizeDateTimeSingle()
        // So we create a date string with timezone information first, and a \DateTime in the current server timezone then.
        return new \DateTime(\date(\DateTime::ATOM, (int) $GLOBALS['SIM_ACCESS_TIME']));
    }

    /**
     * Alias for resetTime.
     *
     * @see resetTime()
     *
     * @param int|null|string|\DateTime $dateInformation
     *
     * @return \DateTime
     */
    public static function getDayStart($dateInformation): \DateTime
    {
        return self::resetTime($dateInformation);
    }

    /**
     * Get the End of the given day.
     *
     * @param int|null|string|\DateTime $dateInformation
     *
     * @return \DateTime
     */
    public static function getDayEnd($dateInformation): \DateTime
    {
        $dateTime = self::getDayStart($dateInformation);
        $dateTime->setTime(23, 59, 59);

        return $dateTime;
    }
}
