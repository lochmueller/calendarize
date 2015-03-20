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

}

