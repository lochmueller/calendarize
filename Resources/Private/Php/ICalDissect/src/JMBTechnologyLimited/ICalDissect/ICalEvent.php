<?php
namespace JMBTechnologyLimited\ICalDissect;


/**
 *
 * @link https://github.com/JMB-Technology-Limited/ICalDissect
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ICalEvent
{

	protected $timeZone;
	protected $timeZoneUTC;
	
	/** @var \DateTime **/
	protected $start;

	/** @var \DateTime **/
	protected $end;
	
	protected $summary;
	
	protected $location;
	
	protected $description;
	
	protected $uid;
	
	protected $deleted = false;

	protected $url;

	protected $ical_rrule;

	protected $exdates = array();

	public function __construct(\DateTimeZone $timeZone = null) {
		$this->timeZoneUTC =  new \DateTimeZone('UTC');
		$this->timeZone = $timeZone ? $timeZone : $this->timeZoneUTC;
	} 
	
	public function processLine($keyword, $value, $keywordProperties = "") {
		if ($keyword == 'UID') {
			$this->uid = $value;
		} else if ($keyword == 'LOCATION') {
			$this->location = $value;
		} else if ($keyword == 'SUMMARY') {
			$this->summary = $value;
		} else if ($keyword == 'DESCRIPTION') {
			$this->description = $value;
		} else if ($keyword == 'URL') {
			$this->url = $value;
		} else if ($keyword == 'DTSTART') {
			$this->start = $this->parseDateTime($value, true);
		} else if ($keyword == 'DTEND') {
			$this->end = $this->parseDateTime($value, false);
		} else if ($keyword == 'METHOD' && $value == 'CANCEL') {
			$this->deleted = true;
		} else if ($keyword == 'STATUS' && $value == 'CANCELLED') {
			$this->deleted = true;
		} else if ($keyword == 'RRULE') {
			$rrule = array();
			foreach(explode(";", $value) as $rruleBit) {
				list($k, $v) = explode("=",$rruleBit,2);
				$rrule[strtoupper($k)] = $v;
			}
			$this->ical_rrule = $rrule;
		} else if ($keyword == "EXDATE") {
			$this->exdates[] = new ICalExDate($value, $keywordProperties);
		}

	}
	
	
	/*
	 * Based on ....
	* @author   Martin Thoma <info@martin-thoma.de>
	* @license  http://www.opensource.org/licenses/mit-license.php  MIT License
	* @link     http://code.google.com/p/ics-parser/
	**/
	protected function parseDateTime($value, $isStart) {
        $value = str_replace('Z', '', $value);
		$pattern  = '/([0-9]{4})';   // 1: YYYY
        $pattern .= '([0-9]{2})';    // 2: MM
        $pattern .= '([0-9]{2})';    // 3: DD
        
		$hasTimePart = false;
		if (strpos($value, "T") > 1) {
			$value = str_replace('T', '', $value);
			$pattern .= '([0-9]{0,2})';  // 4: HH
			$pattern .= '([0-9]{0,2})';  // 5: MM
			$pattern .= '([0-9]{0,2})/'; // 6: SS
			$hasTimePart = true;
		} else {
			$pattern .= '/';
		}
        preg_match($pattern, $value, $date);

        // Unix timestamp can't represent dates before 1970
        if ($date[1] <= 1970) {
            return null;
        }
        // Unix timestamps after 03:14:07 UTC 2038-01-19 might cause an overflow
        // if 32 bit integers are used.
		
		$out = new \DateTime('', $this->timeZone);
		$out->setDate((int)$date[1], (int)$date[2], (int)$date[3]);
		if ($hasTimePart) {
			$out->setTime((int)$date[4], (int)$date[5], (int)$date[6]);
		} else if ($isStart) {
			$out->setTime(0,0,0);
		} else if (!$isStart) {
			$out->setTime(23,59,59);
		}
		if ($this->timeZone->getName() != 'UTC') {
			$out->setTimezone($this->timeZoneUTC);
		}
		return $out;
	}
			
	
	public function getUid() {
		return $this->uid;
	}
	
	public function setUid($uid) {
		$this->uid = $uid;
	}	
	
	public function getStart() {
		return $this->start;
	}

	public function getEnd() {
		return $this->end;
	}

	public function getSummary() {
		return $this->summary;
	}

	public function getLocation() {
		return $this->location;
	}

	public function getDescription() {
		return $this->description;
	}
	
	public function getUrl() {
		return $this->url;
	}

	public function isDeleted() {
		return $this->deleted;
	}

	/**
	 * @return array
	 */
	public function getRRule()
	{
		return $this->ical_rrule;
	}

	/**
	 * @return array
	 */
	public function getExDates()
	{
		return $this->exdates;
	}

	/**
	 * @return ICalExDate
	 */
	public function getExDate($position)
	{
		return $this->exdates[$position];
	}


	/**
	 * @return integer
	 */
	public function getExDatesCount()
	{
		return count($this->exdates);
	}




}

