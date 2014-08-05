<?php
/**
 * Index information
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Domain\Model
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Calendarize\Exception;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;

/**
 * Index information
 *
 * @package    Calendarize
 * @subpackage Domain\Model
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 * @db
 */
class Index extends AbstractModel {

	/**
	 * The unique register key of the used table/model configuration
	 *
	 * @var string
	 * @db
	 */
	protected $uniqueRegisterKey;

	/**
	 * TableName
	 *
	 * @var string
	 * @db
	 */
	protected $foreignTable;

	/**
	 * The Id of the foreign element
	 *
	 * @var int
	 * @db
	 */
	protected $foreignUid;

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
	 * Get the original record for the current index
	 */
	public function getOriginalObject() {
		$configuration = $this->getConfiguration();
		if ($configuration === NULL) {
			throw new Exception('No valid configuration for the current index: ' . $this->getUniqueRegisterKey(), 123678123);
		}
		return $this->getOriginalRecordByConfiguration($configuration, $this->getForeignUid());
	}

	/**
	 * @param $configuration
	 * @param $uid
	 *
	 * @return object
	 */
	protected function getOriginalRecordByConfiguration($configuration, $uid) {
		$query = HelperUtility::getQuery($configuration['modelName']);
		$query->getQuerySettings()
		      ->setRespectStoragePage(FALSE);
		$query->equals('uid', $uid);
		return $query->execute()
		             ->getFirst();
	}

	/**
	 * Get the current configuration
	 *
	 * @return null|array
	 */
	public function getConfiguration() {
		foreach (Register::getRegister() as $key => $configuration) {
			if ($this->getUniqueRegisterKey() == $key) {
				return $configuration;
			}
		}
		return NULL;
	}

	/**
	 * @param int $foreignUid
	 */
	public function setForeignUid($foreignUid) {
		$this->foreignUid = $foreignUid;
	}

	/**
	 * @return int
	 */
	public function getForeignUid() {
		return $this->foreignUid;
	}

	/**
	 * @param string $uniqueRegisterKey
	 */
	public function setUniqueRegisterKey($uniqueRegisterKey) {
		$this->uniqueRegisterKey = $uniqueRegisterKey;
	}

	/**
	 * @return string
	 */
	public function getUniqueRegisterKey() {
		return $this->uniqueRegisterKey;
	}

	/**
	 * @param string $foreignTable
	 */
	public function setForeignTable($foreignTable) {
		$this->foreignTable = $foreignTable;
	}

	/**
	 * @return string
	 */
	public function getForeignTable() {
		return $this->foreignTable;
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
 