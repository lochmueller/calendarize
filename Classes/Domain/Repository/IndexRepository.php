<?php
/**
 * Index repository
 *
 * @package Calendarize\Domain\Repository
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Index repository
 *
 * @author Tim Lochmüller
 */
class IndexRepository extends AbstractRepository {

	/**
	 * Default orderings for index records
	 *
	 * @var array
	 */
	protected $defaultOrderings = array(
		'start_date' => QueryInterface::ORDER_ASCENDING,
		'start_time' => QueryInterface::ORDER_ASCENDING,
	);

	/**
	 * Index types for selection
	 *
	 * @var array
	 */
	protected $indexTypes = array();

	/**
	 * Set the index types
	 *
	 * @param array $types
	 */
	public function setIndexTypes(array $types) {
		$this->indexTypes = $types;
	}

	/**
	 * Create a default query
	 *
	 * @return QueryInterface
	 */
	public function createQuery() {
		$query = parent::createQuery();
		$query->getQuerySettings()
			->setRespectStoragePage(FALSE);
		return $query;
	}

	/**
	 * Find List
	 *
	 * @param int $limit
	 *
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findList($limit = 0) {
		$query = $this->createQuery();
		$constraints = $this->getDefaultConstraints($query);

		// time check
		$orConstraint = array();
		$orConstraint[] = $query->greaterThanOrEqual('start_date', time());
		$orConstraint[] = $query->logicalAnd(array(
			$query->lessThanOrEqual('start_date', time()),
			$query->greaterThanOrEqual('end_date', time())
		));

		$constraints[] = $query->logicalOr($orConstraint);
		$query->matching($query->logicalAnd($constraints));

		if ($limit > 0) {
			$query->setLimit($limit);
		}

		return $query->execute();
	}

	/**
	 * Find by custom search
	 *
	 * @param \DateTime $startDate
	 * @param \DateTime $endDate
	 * @param array     $customSearch
	 *
	 * @return array
	 */
	public function findBySearch(\DateTime $startDate = NULL, \DateTime $endDate = NULL, array $customSearch = array()) {
		$arguments = array(
			'indexIds'     => array(),
			'startDate'    => $startDate,
			'endDate'      => $endDate,
			'customSearch' => $customSearch,
		);
		$signalSlotDispatcher = HelperUtility::getSignalSlotDispatcher();
		$arguments = $signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'Pre', $arguments);

		$query = $this->createQuery();
		$constraints = array();
		if ($arguments['startDate'] instanceof \DateTime) {
			$constraints[] = $query->greaterThan('start_date', $arguments['startDate']);
		}
		if ($arguments['endDate'] instanceof \DateTime) {
			$constraints[] = $query->lessThan('start_date', $arguments['endDate']);
		}
		if ($arguments['indexIds']) {
			$constraints[] = $query->in('foreign_uid', $arguments['indexIds']);
		}
		if ($constraints) {
			$query->matching($query->logicalAnd($constraints));
		}
		$result = array(
			'result' => $query->execute()
		);
		$signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'Post', $result);

		return $result['result'];
	}

	/**
	 * find Year
	 *
	 * @param int $year
	 *
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findYear($year) {
		$query = $this->createQuery();
		$constraints = $this->getDefaultConstraints($query);
		$this->addTimeFrameConstraints($constraints, $query, mktime(0, 0, 0, 0, 0, $year), mktime(0, 0, 0, 0, 0, $year + 1));
		$query->matching($query->logicalAnd($constraints));
		return $query->execute();
	}

	/**
	 * find Month
	 *
	 * @param int $year
	 * @param int $month
	 *
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findMonth($year, $month) {
		$query = $this->createQuery();
		$constraints = $this->getDefaultConstraints($query);
		$startTime = mktime(0, 0, 0, $month, 0, $year);
		$endTime = mktime(0, 0, 0, $month + 1, 0, $year);
		$this->addTimeFrameConstraints($constraints, $query, $startTime, $endTime);
		if (sizeof($constraints)) {
			$query->matching($query->logicalAnd($constraints));
		}
		return $query->execute();
	}

	/**
	 * find Week
	 *
	 * @param int $year
	 * @param int $week
	 *
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findWeek($year, $week) {
		$query = $this->createQuery();
		$constraints = $this->getDefaultConstraints($query);

		$firstDay = DateTimeUtility::convertWeekYear2DayMonthYear($week, $year);
		$timeStampStart = $firstDay->getTimestamp();
		$firstDay->modify('+1 week');
		$timeStampEnd = $firstDay->getTimestamp();
		$this->addTimeFrameConstraints($constraints, $query, $timeStampStart, $timeStampEnd);
		$query->matching($query->logicalAnd($constraints));
		return $query->execute();
	}

	/**
	 * find day
	 *
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 *
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findDay($year, $month, $day) {
		$query = $this->createQuery();
		$constraints = $this->getDefaultConstraints($query);
		$startTime = mktime(0, 0, 0, $month, $day, $year);
		$endTime = mktime(0, 0, 0, $month, $day + 1, $year);
		$this->addTimeFrameConstraints($constraints, $query, $startTime, $endTime);
		$query->matching($query->logicalAnd($constraints));
		return $query->execute();
	}

	/**
	 * Get the default constraint for the queries
	 *
	 * @param QueryInterface $query
	 *
	 * @return array
	 */
	protected function getDefaultConstraints(QueryInterface $query) {
		$constraints = array();
		$constraints[] = $query->in('uniqueRegisterKey', $this->indexTypes);
		return $constraints;
	}

	/**
	 * Add time frame related queries
	 *
	 * @param array          $constraints
	 * @param QueryInterface $query
	 * @param int            $startTime
	 * @param int            $endTime
	 */
	protected function addTimeFrameConstraints(&$constraints, QueryInterface $query, $startTime, $endTime) {
		$orConstraint = array();

		// before - in
		$beforeIn = array(
			$query->lessThan('start_date', $startTime),
			$query->greaterThanOrEqual('end_date', $startTime),
			$query->lessThan('end_date', $endTime),
		);
		$orConstraint[] = $query->logicalAnd($beforeIn);

		// in - in
		$inIn = array(
			$query->greaterThanOrEqual('start_date', $startTime),
			$query->lessThan('end_date', $endTime),
		);
		$orConstraint[] = $query->logicalAnd($inIn);

		// in - after
		$inAfter = array(
			$query->greaterThanOrEqual('start_date', $startTime),
			$query->lessThan('start_date', $endTime),
			$query->greaterThanOrEqual('end_date', $endTime),
		);
		$orConstraint[] = $query->logicalAnd($inAfter);

		// before - after
		$beforeAfter = array(
			$query->lessThan('start_date', $startTime),
			$query->greaterThan('end_date', $endTime),

		);
		$orConstraint[] = $query->logicalAnd($beforeAfter);

		// finish
		$constraints[] = $query->logicalOr($orConstraint);
	}
}