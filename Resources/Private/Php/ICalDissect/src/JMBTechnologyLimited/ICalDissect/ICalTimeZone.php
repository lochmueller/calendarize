<?php
namespace JMBTechnologyLimited\ICalDissect;


/**
 *
 * @link https://github.com/JMB-Technology-Limited/ICalDissect
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class ICalTimeZone
{
	protected $timeZone = 'UTC';

	public function __construct() {
	}

	public function processLine($keyword, $value) {
		if ($keyword == 'TZID') {
			$timezoneIdentifiers = \DateTimeZone::listIdentifiers();
			if (in_array($value, $timezoneIdentifiers)) {
				$this->timeZone = $value;
			}
		}
	}
	
	public function getTimeZoneIdentifier() {
		return $this->timeZone;
	}
	
	public function getTimeZone() {
		return new \DateTimeZone($this->timeZone);
	}
}

