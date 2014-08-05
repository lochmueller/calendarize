<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Domain\Model;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 * @db
 */
class Configuration extends AbstractModel {

	const TYPE_TIME = 'time';

	const TYPE_INCLUDE_GROUP = 'include';

	const TYPE_EXCLUDE_GROUP = 'exclude';

	/**
	 * @var string
	 * @db
	 */
	protected $type = self::TYPE_TIME;

	/**
	 * @var \DateTime
	 * @db
	 */
	protected $startDate;

	/**
	 * @var \DateTime
	 * @db
	 */
	protected $endDate;

	/**
	 * @var int
	 * @db
	 */
	protected $startTime;

	/**
	 * @var int
	 * @db
	 */
	protected $endTime;

	/**
	 * @var boolean
	 * @db
	 */
	protected $allDay;

	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param boolean $allDay
	 */
	public function setAllDay($allDay) {
		$this->allDay = $allDay;
	}

	/**
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
	 * @return \DateTime
	 */
	public function getEndDate() {
		return $this->endDate;
	}

	/**
	 * @param int $endTime
	 */
	public function setEndTime($endTime) {
		$this->endTime = $endTime;
	}

	/**
	 * @return int
	 */
	public function getEndTime() {
		return $this->endTime;
	}

	/**
	 * @param \DateTime $startDate
	 */
	public function setStartDate($startDate) {
		$this->startDate = $startDate;
	}

	/**
	 * @return \DateTime
	 */
	public function getStartDate() {
		return $this->startDate;
	}

	/**
	 * @param int $startTime
	 */
	public function setStartTime($startTime) {
		$this->startTime = $startTime;
	}

	/**
	 * @return int
	 */
	public function getStartTime() {
		return $this->startTime;
	}

}
 