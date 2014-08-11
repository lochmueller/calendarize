<?php
/**
 * Index information
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Domain\Model
 * @author     Tim Lochmüller
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
 * @author     Tim Lochmüller
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
	 * Get the original record by configuration
	 *
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
	 * Set foreign uid
	 *
	 * @param int $foreignUid
	 */
	public function setForeignUid($foreignUid) {
		$this->foreignUid = $foreignUid;
	}

	/**
	 * Get foreign uid
	 *
	 * @return int
	 */
	public function getForeignUid() {
		return $this->foreignUid;
	}

	/**
	 * Set unique register key
	 *
	 * @param string $uniqueRegisterKey
	 */
	public function setUniqueRegisterKey($uniqueRegisterKey) {
		$this->uniqueRegisterKey = $uniqueRegisterKey;
	}

	/**
	 * Get unique register key
	 *
	 * @return string
	 */
	public function getUniqueRegisterKey() {
		return $this->uniqueRegisterKey;
	}

	/**
	 * Set foreign table
	 *
	 * @param string $foreignTable
	 */
	public function setForeignTable($foreignTable) {
		$this->foreignTable = $foreignTable;
	}

	/**
	 * Get foreign table
	 *
	 * @return string
	 */
	public function getForeignTable() {
		return $this->foreignTable;
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
	 * Set end date
	 *
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
	 * Get starte date
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
 