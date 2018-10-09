<?php

namespace JMBTechnologyLimited\ICalDissect;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/ICalDissect
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class RawTest  extends \PHPUnit_Framework_TestCase {


	function testGetDefaultValuesIfKeyNotFound () {
		$parser = new ICalParser();
		$this->assertTrue($parser->parseFromFile(dirname(__FILE__)."/data/rawtest1.ics"));
		$events = $parser->getEvents();
		$this->assertEquals(1, count($events));

		/** @var $event ICalEvent */
		$event = $events[0];

		$rawAll = $event->getRaw();
		$this->assertFalse(isset($rawAll['OUEHUENU']));

		$this->assertTrue(is_array($event->getRaw('OUEHUENU')));
		$this->assertEquals(0, count($event->getRaw('OUEHUENU')));
	}

}
