<?php

namespace JMBTechnologyLimited\ICalDissect;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/ICalDissect
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class LineTest  extends \PHPUnit_Framework_TestCase {



	function dataForTestMultiLineDescription() {
		return array(
				array('IcalParserBasicImportMultiLineDescription.ical','Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Lizard'),
				array('IcalParserBasicImportMultiLineDescription2.ical','Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Lizard:Blue'),
				array('IcalParserBasicImportMultiLineDescription3.ical','Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Go Miaow Lizard:Blue abcdefgtijklmnopqrstuvwxyz abcdefgtijklmnopqrstuvwxyz 12345'),
			);
	}

	/**
     * @dataProvider dataForTestMultiLineDescription
     */		
	function testMultiLineDescription ($filename, $output) {
		$parser = new ICalParser();
		$this->assertTrue($parser->parseFromFile(dirname(__FILE__)."/data/".$filename));
		$events = $parser->getEvents();
		$this->assertEquals(1, count($events));
		$event = $events[0];
		$this->assertEquals($output, $event->getDescription());
	}




}

