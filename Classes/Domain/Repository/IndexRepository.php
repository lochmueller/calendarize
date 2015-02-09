<?php
/**
 * Index repository
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Domain\Repository
 * @author     Tim Lochmüller
 */

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Index repository
 *
 * @package    Calendarize
 * @subpackage Domain\Repository
 * @author     Tim Lochmüller
 */
class IndexRepository extends AbstractRepository {

	/**
	 * @var array
	 */
	protected $defaultOrderings = array(
		'start_date' => QueryInterface::ORDER_ASCENDING,
		'start_time' => QueryInterface::ORDER_ASCENDING,
	);

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
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findList($limit = 0) {
		$query = $this->createQuery();
		$constraints = array();
		$constraints[] = $query->greaterThan('start_date', time());
		$query->matching($query->logicalAnd($constraints));

		if ($limit > 0) {
			$query->setLimit($limit);
		}

		return $query->execute();
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
		$constraints = array();
		$constraints[] = $query->greaterThanOrEqual('start_date', mktime(0, 0, 0, 0, 0, $year));
		$constraints[] = $query->lessThan('start_date', mktime(0, 0, 0, 0, 0, $year + 1));
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
		$constraints = array();
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
		$constraints = array();

		$firstDay = DateTimeUtility::convertWeekYear2DayMonthYear($week, $year);
		$timeStampStart = $firstDay->getTimestamp();
		$firstDay->modify('+1 week');
		$timeStampEnd = $firstDay->getTimestamp();

		$constraints[] = $query->greaterThanOrEqual('start_date', mktime(0, 0, 0, date('m', $timeStampStart), date('d', $timeStampStart), date('Y', $timeStampStart)));
		$constraints[] = $query->lessThan('start_date', mktime(0, 0, 0, date('m', $timeStampEnd), date('d', $timeStampEnd), date('Y', $timeStampEnd)));
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
		$constraints = array();
		$constraints[] = $query->greaterThanOrEqual('start_date', mktime(0, 0, 0, $month, $day, $year));
		$constraints[] = $query->lessThan('start_date', mktime(0, 0, 0, $month, $day + 1, $year));
		$query->matching($query->logicalAnd($constraints));
		return $query->execute();
	}

	/**
	 * @param $constraints
	 * @param $query
	 */
	protected function addTimeFrameConstraints(&$constraints, QueryInterface $query, $startTime, $endTime) {
		$orConstraint = array();

		// before - in
		$beforeIn = array();
		$beforeIn[] = $query->lessThan('start_date', $startTime);
		$beforeIn[] = $query->greaterThanOrEqual('end_date', $startTime);
		$beforeIn[] = $query->lessThan('end_date', $endTime);
		$orConstraint[] = $query->logicalAnd($beforeIn);

		// in - in
		$inIn = array();
		$inIn[] = $query->greaterThanOrEqual('start_date', $startTime);
		$inIn[] = $query->lessThan('end_date', $endTime);
		$orConstraint[] = $query->logicalAnd($inIn);

		// in - after
		$inAfter = array();
		$inAfter[] = $query->greaterThanOrEqual('start_date', $startTime);
		$inAfter[] = $query->lessThan('start_date', $endTime);
		$inAfter[] = $query->greaterThanOrEqual('end_date', $endTime);
		$orConstraint[] = $query->logicalAnd($inAfter);

		// before - after
		$beforeAfter = array();
		$beforeAfter[] = $query->lessThan('start_date', $startTime);
		$beforeAfter[] = $query->greaterThan('end_date', $endTime);
		$orConstraint[] = $query->logicalAnd($beforeAfter);

		// finish
		$constraints[] = $query->logicalOr($orConstraint);
	}
}