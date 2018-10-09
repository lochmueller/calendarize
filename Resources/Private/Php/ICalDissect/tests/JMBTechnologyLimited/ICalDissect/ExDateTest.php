<?php

declare(strict_types=1);

namespace JMBTechnologyLimited\ICalDissect;

/**
 * @see https://github.com/JMB-Technology-Limited/ICalDissect
 *
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 */
class ExDateTest extends \PHPUnit_Framework_TestCase
{
    public function test1()
    {
        $parser = new ICalParser();
        $this->assertTrue($parser->parseFromFile(__DIR__ . '/data/exdate1.ics'));
        $events = $parser->getEvents();
        $this->assertEquals(1, \count($events));

        /** @var $event ICalEvent */
        $event = $events[0];

        $eventRRule = $event->getRrule();
        $rrule = ['FREQ' => 'WEEKLY', 'INTERVAL' => '2', 'BYDAY' => 'TH'];
        $this->assertEquals(\count(\array_keys($rrule)), \count(\array_keys($eventRRule)));
        foreach ($rrule as $k => $v) {
            $this->assertEquals($v, $eventRRule[$k]);
        }

        $this->assertEquals(1, $event->getExDatesCount());
        /** @var $exdate ICalExDate */
        $exdate = $event->getExDate(0);
        $this->assertEquals('TZID=Europe/London', $exdate->getProperties());
        $this->assertEquals('20150226T090000', $exdate->getValues());
    }
}
