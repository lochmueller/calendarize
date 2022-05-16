<?php

/**
 * Configuration Interface for constants.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

/**
 * Configuration Interface for constants.
 */
interface ConfigurationInterface
{
    public const TYPE_TIME = 'time';

    public const TYPE_GROUP = 'group';

    public const TYPE_EXTERNAL = 'external';

    public const HANDLING_INCLUDE = 'include';

    public const HANDLING_EXCLUDE = 'exclude';

    public const HANDLING_OVERRIDE = 'override';

    public const HANDLING_CUTOUT = 'cutout';

    public const FREQUENCY_NONE = '';

    public const FREQUENCY_MINUTELY = 'minutely';

    public const FREQUENCY_HOURLY = 'hourly';

    public const FREQUENCY_DAILY = 'daily';

    public const FREQUENCY_WEEKLY = 'weekly';

    public const FREQUENCY_MONTHLY = 'monthly';

    public const FREQUENCY_YEARLY = 'yearly';

    public const VALID_FREQUENCIES = [
        self::FREQUENCY_MINUTELY,
        self::FREQUENCY_HOURLY,
        self::FREQUENCY_DAILY,
        self::FREQUENCY_WEEKLY,
        self::FREQUENCY_MONTHLY,
        self::FREQUENCY_YEARLY,
    ];

    public const DAY_NONE = '';

    public const DAY_SPECIAL_WEEKDAY = 'weekday';

    public const DAY_SPECIAL_WORKDAY = 'workday';

    public const DAY_SPECIAL_BUSINESS = 'business';

    public const DAY_SPECIAL_WEEKEND = 'weekend';

    public const DAY_MONDAY = 'monday';

    public const DAY_TUESDAY = 'tuesday';

    public const DAY_WEDNESDAY = 'wednesday';

    public const DAY_THURSDAY = 'thursday';

    public const DAY_FRIDAY = 'friday';

    public const DAY_SATURDAY = 'saturday';

    public const DAY_SUNDAY = 'sunday';

    public const RECURRENCE_NONE = '';

    public const RECURRENCE_FIRST = 'first';

    public const RECURRENCE_SECOND = 'second';

    public const RECURRENCE_THIRD = 'third';

    public const RECURRENCE_FOURTH = 'fourth';

    public const RECURRENCE_FIFTH = 'fifth';

    public const RECURRENCE_LAST = 'last';

    public const RECURRENCE_NEXT_TO_LAST = 'next2last';

    public const RECURRENCE_THIRD_LAST = 'thirdLast';

    public const STATE_DEFAULT = 'default';

    public const STATE_CANCELED = 'canceled';

    public const END_DYNAMIC_1_DAY = '1day';

    public const END_DYNAMIC_1_WEEK = '1week';

    public const END_DYNAMIC_END_WEEK = 'end_week';

    public const END_DYNAMIC_END_MONTH = 'end_month';

    public const END_DYNAMIC_END_YEAR = 'end_year';
}
