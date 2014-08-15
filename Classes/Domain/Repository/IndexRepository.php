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
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
	 */
	public function createQuery() {
		$query = parent::createQuery();
		$query->getQuerySettings()
			->setRespectStoragePage(FALSE);
		return $query;
	}

	/**
	 * Get the default constraints for all queries
	 *
	 * @return array
	 */
	protected function getDefaultConstraints() {
		$query = $this->createQuery();
		$constraints = array();
		$constraints[] = $query->greaterThan('start_date', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
		return $constraints;
	}

	/**
	 * Find List
	 *
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findList() {
		$query = $this->createQuery();
		$constraints = $this->getDefaultConstraints();
		$query->matching($query->logicalAnd($constraints));
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
		$constraints = $this->getDefaultConstraints();
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
		$constraints = $this->getDefaultConstraints();
		$constraints[] = $query->greaterThanOrEqual('start_date', mktime(0, 0, 0, $month, 0, $year));
		$constraints[] = $query->lessThan('start_date', mktime(0, 0, 0, $month + 1, 0, $year));
		$query->matching($query->logicalAnd($constraints));
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
		$constraints = $this->getDefaultConstraints();

		/**
		 * @todo implement week convert
		 */
		//$constraints[] = $query->greaterThanOrEqual('start_date', mktime(0, 0, 0, $month, 0, $year));
		//$constraints[] = $query->lessThan('start_date', mktime(0, 0, 0, $month + 1, 0, $year));
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
		$constraints = $this->getDefaultConstraints();
		$constraints[] = $query->greaterThanOrEqual('start_date', mktime(0, 0, 0, $month, $day, $year));
		$constraints[] = $query->lessThan('start_date', mktime(0, 0, 0, $month, $day + 1, $year));
		$query->matching($query->logicalAnd($constraints));
		return $query->execute();
	}
}