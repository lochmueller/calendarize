<?php

namespace JMBTechnologyLimited\ICalDissect;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/ICalDissect
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class LineTest extends \PHPUnit_Framework_TestCase
{
    public function dataForTestMultiLineDescription()
    {
        return [
                ['IcalParserBasicImportMultiLineDescription.ical', 'Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Lizard'],
                ['IcalParserBasicImportMultiLineDescription2.ical', 'Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Lizard:Blue'],
                ['IcalParserBasicImportMultiLineDescription3.ical', 'Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Dog Cat Go Miaow Lizard:Blue abcdefgtijklmnopqrstuvwxyz abcdefgtijklmnopqrstuvwxyz 12345'],
            ];
    }

    /**
     * @dataProvider dataForTestMultiLineDescription
     */
    public function testMultiLineDescription($filename, $output): void
    {
        $parser = new ICalParser();
        $this->assertTrue($parser->parseFromFile(dirname(__FILE__) . '/data/' . $filename));
        $events = $parser->getEvents();
        $this->assertEquals(1, count($events));
        $event = $events[0];
        $this->assertEquals($output, $event->getDescription());

        $rawAll = $event->getRaw();
        $this->assertTrue(isset($rawAll['DESCRIPTION']));

        $rawAllDesc =  $rawAll['DESCRIPTION'];
        $this->assertEquals($output, $rawAllDesc[0]);

        $rawKey = $event->getRaw('DESCRIPTION');
        $this->assertEquals($output, $rawKey[0]);
    }
}
