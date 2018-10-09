<?php

declare(strict_types=1);

namespace JMBTechnologyLimited\ICalDissect;

/**
 * @see https://github.com/JMB-Technology-Limited/ICalDissect
 *
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 */
class ICalTimeZone
{
    protected $timeZone = 'UTC';

    public function __construct()
    {
    }

    public function processLine($keyword, $value)
    {
        if ('TZID' === $keyword) {
            $timezoneIdentifiers = \DateTimeZone::listIdentifiers();
            if (\in_array($value, $timezoneIdentifiers, true)) {
                $this->timeZone = $value;
            }
        }
    }

    public function getTimeZoneIdentifier()
    {
        return $this->timeZone;
    }

    public function getTimeZone()
    {
        return new \DateTimeZone($this->timeZone);
    }
}
