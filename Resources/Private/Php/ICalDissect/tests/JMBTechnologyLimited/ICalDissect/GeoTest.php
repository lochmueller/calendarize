<?php

namespace JMBTechnologyLimited\ICalDissect;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/ICalDissect
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GeoTest  extends \PHPUnit_Framework_TestCase {

    function dataForTestGeo1() {
        return array(
            array('GeoExample.ics',37.5739497,-85.7399606),
        );
    }

    /**
     * @dataProvider dataForTestGeo1
     */
    function testGeo1 ($filename, $lat, $lng) {
        $parser = new ICalParser();
        $this->assertTrue($parser->parseFromFile(dirname(__FILE__)."/data/".$filename));

        $events = $parser->getEvents();
        $this->assertEquals(1, count($events));

        /** @var $event ICalEvent */
        $event = $events[0];

        $this->assertTrue($event->hasGeo());
        $this->assertEquals($lat, $event->getGeoLat());
        $this->assertEquals($lng, $event->getGeoLng());

    }

    function dataForTestNoGeo1() {
        return array(
            array('exdate1.ics'),
        );
    }

    /**
     * @dataProvider dataForTestNoGeo1
     */
    function testNoGeo1 ($filename) {
        $parser = new ICalParser();
        $this->assertTrue($parser->parseFromFile(dirname(__FILE__)."/data/".$filename));

        $events = $parser->getEvents();
        $this->assertEquals(1, count($events));

        /** @var $event ICalEvent */
        $event = $events[0];

        $this->assertFalse($event->hasGeo());
    }

}

