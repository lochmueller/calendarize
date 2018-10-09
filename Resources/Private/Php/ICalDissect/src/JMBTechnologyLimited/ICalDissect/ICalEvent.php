<?php

declare(strict_types=1);

namespace JMBTechnologyLimited\ICalDissect;

/**
 * @see https://github.com/JMB-Technology-Limited/ICalDissect
 *
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 */
class ICalEvent
{
    protected $timeZone;
    protected $timeZoneUTC;

    /**
     * Start, in UTC.
     *
     * @var \DateTime **/
    protected $start;

    /**
     * End, in UTC.
     *
     * @var \DateTime **/
    protected $end;

    protected $summary;

    protected $location;

    protected $description;

    protected $uid;

    protected $deleted = false;

    protected $url;

    protected $ical_rrule;

    protected $exdates = [];

    protected $geoLat;

    protected $geoLng;

    protected $raw = [];

    public function __construct(\DateTimeZone $timeZone = null)
    {
        $this->timeZoneUTC = new \DateTimeZone('UTC');
        $this->timeZone = $timeZone ? $timeZone : $this->timeZoneUTC;
    }

    public function processLine($keyword, $value, $keywordProperties = '')
    {
        if ('UID' === $keyword) {
            $this->uid = $value;
        } elseif ('LOCATION' === $keyword) {
            $this->location = $value;
        } elseif ('SUMMARY' === $keyword) {
            $this->summary = $value;
        } elseif ('DESCRIPTION' === $keyword) {
            $this->description = $value;
        } elseif ('URL' === $keyword) {
            $this->url = $value;
        } elseif ('DTSTART' === $keyword) {
            $this->start = $this->parseDateTime($value, true, $keywordProperties);
        } elseif ('DTEND' === $keyword) {
            $this->end = $this->parseDateTime($value, false, $keywordProperties);
        } elseif ('METHOD' === $keyword && 'CANCEL' === $value) {
            $this->deleted = true;
        } elseif ('STATUS' === $keyword && 'CANCELLED' === $value) {
            $this->deleted = true;
        } elseif ('RRULE' === $keyword) {
            $rrule = [];
            foreach (\explode(';', $value) as $rruleBit) {
                list($k, $v) = \explode('=', $rruleBit, 2);
                $rrule[\mb_strtoupper($k)] = $v;
            }
            $this->ical_rrule = $rrule;
        } elseif ('EXDATE' === $keyword) {
            $this->exdates[] = new ICalExDate($value, $keywordProperties);
        } elseif ('GEO' === $keyword) {
            $bits = \explode(';', $value);
            if (2 === \count($bits)) {
                $this->geoLat = $bits[0];
                $this->geoLng = $bits[1];
            }
        }

        if (!isset($this->raw[\mb_strtoupper($keyword)])) {
            $this->raw[\mb_strtoupper($keyword)] = [];
        }
        $this->raw[\mb_strtoupper($keyword)][] = $value;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * In UTC.
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * In UTC.
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @return array
     */
    public function getRRule()
    {
        return $this->ical_rrule;
    }

    /**
     * @return array
     */
    public function getExDates()
    {
        return $this->exdates;
    }

    /**
     * @param mixed $position
     *
     * @return ICalExDate
     */
    public function getExDate($position)
    {
        return $this->exdates[$position];
    }

    /**
     * @return int
     */
    public function getExDatesCount()
    {
        return \count($this->exdates);
    }

    /**
     * Returns raw line data for this event.
     *
     * @parameter $keyword pass keyword you want, or null to get all data as an array
     *
     * If $keyword parameter is passed, values for that only will be returned.
     * This will always be in array form, even if there was no data. (ie an empty array)
     * This is to make it easy to loop over
     *
     * If $keyword parameter is null, all values will be returned in an array of arrays.
     *
     * Note all keywords are always in upper case.
     *
     * @param null|mixed $keyword
     *
     * @return array
     */
    public function getRaw($keyword = null)
    {
        if ($keyword) {
            return isset($this->raw[\mb_strtoupper($keyword)]) ? $this->raw[\mb_strtoupper($keyword)] : [];
        }

        return $this->raw;
    }

    public function hasGeo()
    {
        return (bool) ($this->geoLat && $this->geoLng);
    }

    public function getGeoLat()
    {
        return $this->geoLat;
    }

    public function getGeoLng()
    {
        return $this->geoLng;
    }

    /*
     * Based on http://code.google.com/p/ics-parser/, MIT License
     * Changed for Timezones.
    **/
    protected function parseDateTime($value, $isStart, $keywordProperties)
    {
        // We should be doing something like this - if it's not UTC it's a floating time and we should look at pre-set timezone or parameter timezone.
        // https://tools.ietf.org/html/rfc5545#section-3.3.5
        $isUTC = 'Z' === \mb_substr($value, -1);

        $value = \str_replace('Z', '', $value);
        $pattern = '/([0-9]{4})';   // 1: YYYY
        $pattern .= '([0-9]{2})';    // 2: MM
        $pattern .= '([0-9]{2})';    // 3: DD

        $hasTimePart = false;
        if (\mb_strpos($value, 'T') > 1) {
            $value = \str_replace('T', '', $value);
            $pattern .= '([0-9]{0,2})';  // 4: HH
            $pattern .= '([0-9]{0,2})';  // 5: MM
            $pattern .= '([0-9]{0,2})/'; // 6: SS
            $hasTimePart = true;
        } else {
            $pattern .= '/';
        }
        \preg_match($pattern, $value, $date);

        // Unix timestamp can't represent dates before 1970
        if ($date[1] <= 1970) {
            return;
        }
        // Unix timestamps after 03:14:07 UTC 2038-01-19 might cause an overflow
        // if 32 bit integers are used.

        $out = new \DateTime('', $this->timeZoneUTC);
        if (!$isUTC) {
            // Is Timezone in Keyword Properties?
            if ('TZID=' === \mb_substr($keywordProperties, 0, 5)) {
                $timeZone = new \DateTimeZone(\mb_substr($keywordProperties, 5));
                $out->setTimezone($timeZone);
            }
        }
        $out->setDate((int) $date[1], (int) $date[2], (int) $date[3]);
        if ($hasTimePart) {
            $out->setTime((int) $date[4], (int) $date[5], (int) $date[6]);
        } elseif ($isStart) {
            $out->setTime(0, 0, 0);
        } elseif (!$isStart) {
            $out->setTime(23, 59, 59);
        }
        if (!$isUTC) {
            $out->setTimezone($this->timeZoneUTC);
        }

        return $out;
    }
}
