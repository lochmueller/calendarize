<?php

declare(strict_types=1);

namespace JMBTechnologyLimited\ICalDissect;

/**
 * @see https://github.com/JMB-Technology-Limited/ICalDissect
 *
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 */
class GeoTest extends \PHPUnit_Framework_TestCase
{
    public function dataForTestGeo1()
    {
        return [
            ['GeoExample.ics', 37.5739497, -85.7399606],
        ];
    }

    /**
     * @dataProvider dataForTestGeo1
     *
     * @param mixed $filename
     * @param mixed $lat
     * @param mixed $lng
     */
    public function testGeo1($filename, $lat, $lng)
    {
        $parser = new ICalParser();
        $this->assertTrue($parser->parseFromFile(__DIR__ . '/data/' . $filename));

        $events = $parser->getEvents();
        $this->assertEquals(1, \count($events));

        /** @var $event ICalEvent */
        $event = $events[0];

        $this->assertTrue($event->hasGeo());
        $this->assertEquals($lat, $event->getGeoLat());
        $this->assertEquals($lng, $event->getGeoLng());
    }

    public function dataForTestNoGeo1()
    {
        return [
            ['exdate1.ics'],
        ];
    }

    /**
     * @dataProvider dataForTestNoGeo1
     *
     * @param mixed $filename
     */
    public function testNoGeo1($filename)
    {
        $parser = new ICalParser();
        $this->assertTrue($parser->parseFromFile(__DIR__ . '/data/' . $filename));

        $events = $parser->getEvents();
        $this->assertEquals(1, \count($events));

        /** @var $event ICalEvent */
        $event = $events[0];

        $this->assertFalse($event->hasGeo());
    }
}
