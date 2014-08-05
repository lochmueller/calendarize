<?php
/**
 * Configuration for time options
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Domain\Model
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Domain\Model;

/**
 * Configuration for time options
 *
 * @package    Calendarize
 * @subpackage Domain\Model
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 * @db
 */
class Configuration extends AbstractModel {

	const TYPE_TIME = 'time';

	const TYPE_INCLUDE_GROUP = 'include';

	const TYPE_EXCLUDE_GROUP = 'exclude';

	/**
	 * Type
	 *
	 * @var string
	 * @db
	 */
	protected $type = self::TYPE_TIME;

	/**
	 * Start date
	 *
	 * @var \DateTime
	 * @db
	 */
	protected $startDate;

	/**
	 * End date
	 *
	 * @var \DateTime
	 * @db
	 */
	protected $endDate;

	/**
	 * Start time
	 *
	 * @var int
	 * @db
	 */
	protected $startTime;

	/**
	 * End time
	 *
	 * @var int
	 * @db
	 */
	protected $endTime;

	/**
	 * AllDay
	 *
	 * @var boolean
	 * @db
	 */
	protected $allDay;

	/**
	 * Set type
	 *
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Set all day
	 *
	 * @param boolean $allDay
	 */
	public function setAllDay($allDay) {
		$this->allDay = $allDay;
	}

	/**
	 * Get all day
	 *
	 * @return boolean
	 */
	public function getAllDay() {
		return $this->allDay;
	}

	/**
	 * @param \DateTime $endDate
	 */
	public function setEndDate($endDate) {
		$this->endDate = $endDate;
	}

	/**
	 * Get end date
	 *
	 * @return \DateTime
	 */
	public function getEndDate() {
		return $this->endDate;
	}

	/**
	 * Set end time
	 *
	 * @param int $endTime
	 */
	public function setEndTime($endTime) {
		$this->endTime = $endTime;
	}

	/**
	 * Get end time
	 *
	 * @return int
	 */
	public function getEndTime() {
		return $this->endTime;
	}

	/**
	 * Set start date
	 *
	 * @param \DateTime $startDate
	 */
	public function setStartDate($startDate) {
		$this->startDate = $startDate;
	}

	/**
	 * Get start date
	 *
	 * @return \DateTime
	 */
	public function getStartDate() {
		return $this->startDate;
	}

	/**
	 * Set start time
	 *
	 * @param int $startTime
	 */
	public function setStartTime($startTime) {
		$this->startTime = $startTime;
	}

	/**
	 * Get start time
	 *
	 * @return int
	 */
	public function getStartTime() {
		return $this->startTime;
	}

}
 