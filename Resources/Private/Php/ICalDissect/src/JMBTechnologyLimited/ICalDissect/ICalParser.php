<?php

declare(strict_types=1);

namespace JMBTechnologyLimited\ICalDissect;

/**
 * @see https://github.com/JMB-Technology-Limited/ICalDissect
 *
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 */
class ICalParser
{
    /** @var ICalTimeZone * */
    protected $timezone;

    protected $events = [];

    public function __construct()
    {
        $this->timezone = new ICalTimeZone();
    }

    /**
     * @param {string} $filename The path to the iCal-file
     *
     * @return object The iCal-Object
     */
    public function parseFromFile($filename)
    {
        if (!$filename) {
            return false;
        }

        $lines = \file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (\count($lines) < 1) {
            return false;
        }

        if (false === \mb_stristr($lines[0], 'BEGIN:VCALENDAR')) {
            return false;
        }

        // pass1: put multi lines back into one line.
        $linesCompacted = [];
        foreach ($lines as $line) {
            if (' ' === \mb_substr($line, 0, 1)) {
                $linesCompacted[\count($linesCompacted) - 1] .= \mb_substr($line, 1);
            } else {
                $linesCompacted[] = \trim($line);
            }
        }

        //pass2: turn lines into formatted data
        $rawLines = [];
        foreach ($linesCompacted as $line) {
            $bits = \explode(':', $line, 2);
            $kbits = \explode(';', $bits[0], 2);
            $value = 2 === \count($bits) ? $bits[1] : '';
            // http://www.ietf.org/rfc/rfc2445.txt section 4.3.11
            $value = \str_replace('\\\\', '\\', $value);
            $value = \str_replace('\\N', "\n", $value);
            $value = \str_replace('\\n', "\n", $value);
            $value = \str_replace('\\;', ';', $value);
            $value = \str_replace('\,', ',', $value);
            $rawLines[] = [
                        'KEYWORD' => $kbits[0],
                        'KEYWORDOPTIONS' => 2 === \count($kbits) ? $kbits[1] : '',
                        'VALUE' => $value,
                    ];
        }

        // pass3: finally parse lines
        $stack = [];
        foreach ($rawLines as $line) {
            if ('BEGIN' === $line['KEYWORD']) {
                $stack[] = $line['VALUE'];
                if ('VEVENT' === $line['VALUE']) {
                    $this->events[] = new ICalEvent($this->timezone->getTimeZone());
                }
            } elseif ('END' === $line['KEYWORD']) {
                // TODO check VALUE and last stack match
                \array_pop($stack);
            } else {
                $currentlyIn = $stack[\count($stack) - 1];
                //print $currentlyIn." with K ".$line['KEYWORD']."\n";
                if ('VEVENT' === $currentlyIn) {
                    $this->events[\count($this->events) - 1]->processLine($line['KEYWORD'], $line['VALUE'], $line['KEYWORDOPTIONS']);
                } elseif ('VTIMEZONE' === $currentlyIn) {
                    $this->timezone->processLine($line['KEYWORD'], $line['VALUE'], $line['KEYWORDOPTIONS']);
                }
            }
        }

        //die();

        return true;
    }

    /**
     * This is only for debugging.
     * This class ensures all dates returned are in UTC, whatever the input time zone was.
     *
     * @return type
     */
    public function getTimeZoneIdentifier()
    {
        return $this->timezone->getTimeZoneIdentifier();
    }

    public function getEvents()
    {
        return $this->events;
    }
}
