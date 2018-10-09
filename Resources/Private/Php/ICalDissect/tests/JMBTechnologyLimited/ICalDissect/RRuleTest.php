<?php

declare(strict_types=1);

namespace JMBTechnologyLimited\ICalDissect;

/**
 * @see https://github.com/JMB-Technology-Limited/ICalDissect
 *
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 */
class RRuleTest extends \PHPUnit_Framework_TestCase
{
    public function dataForTestRRule()
    {
        return [
            ['IcalParserRRule1.ics', ['FREQ' => 'WEEKLY', 'BYDAY' => 'WE']],
            ['IcalParserRRule2.ics', ['FREQ' => 'WEEKLY', 'BYDAY' => 'TH', 'COUNT' => 5]],
        ];
    }

    /**
     * @dataProvider dataForTestRRule
     *
     * @param mixed $filename
     * @param mixed $rrule
     */
    public function testGetByPosition($filename, $rrule)
    {
        $parser = new ICalParser();
        $this->assertTrue($parser->parseFromFile(__DIR__ . '/data/' . $filename));
        $events = $parser->getEvents();
        $this->assertEquals(1, \count($events));
        $event = $events[0];

        $eventRRule = $event->getRrule();
        $this->assertEquals(\count(\array_keys($rrule)), \count(\array_keys($eventRRule)));
        foreach ($rrule as $k => $v) {
            $this->assertEquals($v, $eventRRule[$k]);
        }
    }

    /**
     * @dataProvider dataForTestRRule
     *
     * @param mixed $filename
     * @param mixed $rrule
     */
    public function testGetByArray($filename, $rrule)
    {
        $parser = new ICalParser();
        $this->assertTrue($parser->parseFromFile(__DIR__ . '/data/' . $filename));
        $events = $parser->getEvents();
        $this->assertEquals(1, \count($events));
        $event = $events[0];

        $eventRRule = $event->getRrule();
        $this->assertEquals(\count(\array_keys($rrule)), \count(\array_keys($eventRRule)));
        foreach ($rrule as $k => $v) {
            $this->assertEquals($v, $eventRRule[$k]);
        }
    }
}
