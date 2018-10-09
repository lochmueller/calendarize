<?php

namespace JMBTechnologyLimited\ICalDissect;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/ICalDissect
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TimezoneTest  extends \PHPUnit_Framework_TestCase {
	
	function dataForTestTimeZone1() {
		return array(
				array('London.ical','Europe/London'),
				array('UTC.ical','UTC'),
				array('BasicICAL.ical','UTC'),
			);
	}
	
	/**
     * @dataProvider dataForTestTimeZone1
     */	
	function testTimeZone1 ($filename, $timeZone) {
		$parser = new ICalParser();
		$this->assertTrue($parser->parseFromFile(dirname(__FILE__)."/data/".$filename));
		$this->assertEquals($timeZone, $parser->getTimeZoneIdentifier());
	}

    function testTimeZoneFromFileToEvent1() {
        $parser = new ICalParser();
        $this->assertTrue($parser->parseFromFile(dirname(__FILE__)."/data/TimeZone1.ics"));
        $events = $parser->getEvents();
        $this->assertEquals(1, count($events));
        $event = $events[0];

        $this->assertEquals('2016-10-11T16:30:00+00:00', $event->getStart()->format('c'));
        $this->assertEquals('2016-10-11T20:00:00+00:00', $event->getEnd()->format('c'));

    }

    function testTimeZoneFromMeetupToEvent1() {
        $parser = new ICalParser();
        $this->assertTrue($parser->parseFromFile(dirname(__FILE__)."/data/Meetup1.ics"));
        $events = $parser->getEvents();
        $this->assertEquals(1, count($events));
        $event = $events[0];

        $this->assertEquals('2013-10-17T18:00:00+00:00', $event->getStart()->format('c'));
        $this->assertEquals('2013-10-17T21:00:00+00:00', $event->getEnd()->format('c'));

    }

}

