<?php

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
    public const SECONDS_SECOND = 1;

    /**
     * One minute in seconds.
     */
    public const SECONDS_MINUTE = 60;

    /**
     * One hour in seconds.
     */
    public const SECONDS_HOUR = 3600;

    /**
     * One day in seconds.
     */
    public const SECONDS_DAY = 86400;

    /**
     * One week in seconds.
     */
    public const SECONDS_WEEK = 604800;

    /**
     * One quartar in seconds (90 days).
     */
    public const SECONDS_QUARTER = 7776000;

    /**
     * One year in seconds (365 days).
     */
    public const SECONDS_YEAR = 31536000;

    /**
     * One decade in seconds (base on a 365 days year).
     */
    public const SECONDS_DECADE = 315360000;

    /**
     * Convert a Week/Year combination to a DateTime of the first day of week.
     */
    public static function convertWeekYear2DayMonthYear(int $week, int $year, int $startDay = 1): \DateTime
    {
        $date = self::getNow();
        $date->setTime(0, 0);
        $date->setISODate($year, $week, $startDay);

        return $date;
    }

    /**
     * Time zone is set by the TYPO3 core.
     *
     * @see \TYPO3\CMS\Core\Core\Bootstrap->setDefaultTimezone()
     */
    public static function getTimeZone(): \DateTimeZone
    {
        return new \DateTimeZone(date_default_timezone_get());
    }

    /**
     * Time zone that is always UTC.
     */
    public static function getUtcTimeZone(): \DateTimeZone
    {
        return new \DateTimeZone('UTC');
    }

    /**
     * Get the time seconds of the given date (TYPO3 Backend style).
     */
    public static function getDaySecondsOfDateTime(\DateTimeInterface $dateTime): int
    {
        $hours = (int)$dateTime->format('G');
        $minutes = $hours * self::SECONDS_MINUTE + (int)$dateTime->format('i');

        return $minutes * self::SECONDS_MINUTE + (int)$dateTime->format('s');
    }

    /**
     * Get the time seconds of the given date (TYPO3 Backend style) in the server timezone.
     */
    public static function getNormalizedDaySecondsOfDateTime(\DateTimeInterface $dateTime): int
    {
        $date = self::normalizeDateTimeSingle($dateTime);

        return self::getDaySecondsOfDateTime($date);
    }

    /**
     * Sets the time seconds on the given date.
     */
    public static function setSecondsOfDateTime(\DateTime $date, int $seconds): \DateTime
    {
        $date = clone $date;
        $date->setTime(0, 0);
        $date->modify("+$seconds seconds");

        return $date;
    }

    /**
     * Get a normalize date time object.
     */
    public static function normalizeDateTime(
        ?int $day = null,
        ?int $month = null,
        ?int $year = null,
    ): \DateTime {
        $date = self::getNow();
        // Check if this date should handle always in UTC
        // $date->setTimezone(self::getUtcTimeZone());
        if (!MathUtility::canBeInterpretedAsInteger($year) || 0 === $year) {
            $year = $date->format('Y');
        }
        if (!MathUtility::canBeInterpretedAsInteger($month) || 0 === $month) {
            $month = $date->format('m');
        }
        if (!MathUtility::canBeInterpretedAsInteger($day) || 0 === $day) {
            $day = $date->format('d');
        }
        $date->setDate((int)$year, (int)$month, (int)$day);
        $date->setTime(0, 0);
        if ($date->format('m') > $month) {
            $date->modify('last day of last month');
        } elseif ($date->format('m') < $month) {
            $date->modify('first day of next month');
        }

        return $date;
    }

    /**
     * Normalize quarter.
     */
    public static function normalizeQuarter(?int $quarter = null): int
    {
        if (null === $quarter) {
            $quarter = self::getQuarter(self::getNow());
        }

        return MathUtility::forceIntegerInRange((int)$quarter, 1, 4);
    }

    /**
     * Normalize quarter.
     */
    public static function getQuarter(\DateTimeInterface $date): int
    {
        $month = (int)$date->format('n');

        return (int)ceil($month / 3);
    }

    /**
     * Reset the DateTime.
     */
    public static function resetTime(string|\DateTimeInterface|null $dateTime = null): \DateTime
    {
        $dateTime = self::normalizeDateTimeSingle($dateTime);
        $dateTime->setTime(0, 0);

        return $dateTime;
    }

    /**
     * Get a normalized date time object in a specific timezone.
     *
     * @throws \Exception
     */
    public static function normalizeDateTimeSingle(
        int|string|\DateTimeInterface|null $dateInformation = null,
        ?\DateTimeZone $timezone = null,
    ): \DateTime {
        $timezone = $timezone ?? self::getTimeZone();
        $date = self::getNow();

        if ($dateInformation instanceof \DateTimeInterface) {
            // Convert DateTimeInterface to a DateTime object
            $date = \DateTime::createFromFormat(
                \DateTimeInterface::ATOM,
                $dateInformation->format(\DateTimeInterface::ATOM),
                $timezone,
            );
        } elseif (MathUtility::canBeInterpretedAsInteger($dateInformation)) {
            // http://php.net/manual/en/datetime.construct#refsect1-datetime.construct-parameters :
            // The $timezone parameter and the current timezone are ignored [i.e. set to UTC] when
            // the $time parameter [...] is a UNIX timestamp (e.g. @946684800) [...]
            $date = new \DateTime("@$dateInformation");
        } elseif (\is_string($dateInformation) && \in_array($dateInformation[0], ['-', '+'])) {
            $date = self::getNow();
            $date->modify($dateInformation);
        } elseif (\is_string($dateInformation)) {
            // Add timezone explicitly here, so that it does not depend on the "current timezone".
            $date = new \DateTime($dateInformation, $timezone);
        }

        // Change timezone
        $date->setTimezone($timezone);

        return $date;
    }

    /**
     * Get the current date (normalized optimized for queries, because SIM_ACCESS_TIME is rounded to minutes)
     * in the current timezone.
     */
    public static function getNow(): \DateTime
    {
        // NOTE that new \DateTime('@timestamp') does NOT work - @see comment in normalizeDateTimeSingle()
        // So we create a date string with timezone information first, and a \DateTime in the current
        // server timezone then.
        return new \DateTime(date('Y-m-d\TH:i:sP', (int)$GLOBALS['SIM_ACCESS_TIME']), self::getTimeZone());
    }

    /**
     * Alias for resetTime.
     *
     * @see resetTime()
     */
    public static function getDayStart(string|\DateTimeInterface $dateInformation): \DateTime
    {
        return self::resetTime($dateInformation);
    }

    /**
     * Get the End of the given day.
     */
    public static function getDayEnd(string|\DateTimeInterface $dateInformation): \DateTime
    {
        $dateTime = self::resetTime($dateInformation);
        $dateTime->setTime(23, 59, 59);

        return $dateTime;
    }
}
