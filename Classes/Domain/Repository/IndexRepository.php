<?php
/**
 * Index repository
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Domain\Repository;

use Exception;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Index repository
 */
class IndexRepository extends AbstractRepository
{

    /**
     * Default orderings for index records
     *
     * @var array
     */
    protected $defaultOrderings = [
        'start_date' => QueryInterface::ORDER_ASCENDING,
        'start_time' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * Index types for selection
     *
     * @var array
     */
    protected $indexTypes = [];

    /**
     * Set the index types
     *
     * @param array $types
     */
    public function setIndexTypes(array $types)
    {
        $this->indexTypes = $types;
    }

    /**
     * Override page ids
     *
     * @var array
     */
    protected $overridePageIds = [];

    /**
     * Override page IDs
     *
     * @param array $overridePageIds
     */
    public function setOverridePageIds($overridePageIds)
    {
        $this->overridePageIds = $overridePageIds;
    }

    /**
     * Find List
     *
     * @param int $limit
     * @param int|string $listStartTime
     * @param int $startOffsetHours
     * @param int $overrideStartDate
     * @param int $overrideEndDate
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findList(
        $limit = 0,
        $listStartTime = 0,
        $startOffsetHours = 0,
        $overrideStartDate = 0,
        $overrideEndDate = 0
    ) {
    
        if ($overrideStartDate > 0) {
            $startTimestamp = $overrideStartDate;
        } else {
            $now = DateTimeUtility::getNow();
            if ($listStartTime != 'now') {
                $now->setTime(0, 0, 0);
            }
            $now->modify($startOffsetHours . ' hours');
            $startTimestamp = $now->getTimestamp();
        }
        $endTimestamp = null;
        if ($overrideEndDate > 0) {
            $endTimestamp = $overrideEndDate;
        }

        $result = $this->findByTimeSlot($startTimestamp, $endTimestamp);
        if ($limit > 0) {
            $query = $result->getQuery();
            $query->setLimit($limit);
            $result = $query->execute();
        }
        return $result;
    }

    /**
     * Find by custom search
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array $customSearch
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findBySearch(\DateTime $startDate = null, \DateTime $endDate = null, array $customSearch = [])
    {
        $arguments = [
            'indexIds' => [],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'customSearch' => $customSearch,
            'indexTypes' => $this->indexTypes,
        ];
        $arguments = $this->callSignal(__CLASS__, __FUNCTION__ . 'Pre', $arguments);

        $query = $this->createQuery();
        $constraints = $this->getDefaultConstraints($query);

        $this->addTimeFrameConstraints(
            $constraints,
            $query,
            $startDate instanceof \DateTime ? DateTimeUtility::getDayStart($startDate) : null,
            $endDate instanceof \DateTime ? DateTimeUtility::getDayEnd($endDate) : null
        );

        if ($arguments['indexIds']) {
            $constraints[] = $query->in('foreign_uid', $arguments['indexIds']);
        }
        $result = [
            'result' => $this->matchAndExecute($query, $constraints)
        ];

        $result = $this->callSignal(__CLASS__, __FUNCTION__ . 'Post', $result);

        return $result['result'];
    }

    /**
     * Find by traversing information
     *
     * @param Index $index
     * @param bool|true $future
     * @param bool|false $past
     * @param int $limit
     * @param string $sort
     * @param bool $useIndexTime
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByTraversing(
        Index $index,
        $future = true,
        $past = false,
        $limit = 100,
        $sort = QueryInterface::ORDER_ASCENDING,
        $useIndexTime = false
    ) {
    
        if (!$future && !$past) {
            return [];
        }
        $query = $this->createQuery();

        $now = DateTimeUtility::getNow()
            ->getTimestamp();
        if ($useIndexTime) {
            $now = $index->getStartDate()->getTimestamp();
        }

        $constraints = [];
        $constraints[] = $query->logicalNot($query->equals('uid', $index->getUid()));
        $constraints[] = $query->equals('foreignTable', $index->getForeignTable());
        $constraints[] = $query->equals('foreignUid', $index->getForeignUid());
        if (!$future) {
            $constraints[] = $query->lessThanOrEqual('startDate', $now);
        }
        if (!$past) {
            $constraints[] = $query->greaterThanOrEqual('startDate', $now);
        }

        $query->setLimit($limit);
        $sort = $sort === QueryInterface::ORDER_ASCENDING ? QueryInterface::ORDER_ASCENDING : QueryInterface::ORDER_DESCENDING;
        $query->setOrderings($this->getSorting($sort));
        return $this->matchAndExecute($query, $constraints);
    }

    /**
     * find Year
     *
     * @param int $year
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findYear($year)
    {
        return $this->findByTimeSlot(mktime(0, 0, 0, 1, 1, $year), mktime(0, 0, 0, 1, 1, $year + 1) - 1);
    }

    /**
     * find Month
     *
     * @param int $year
     * @param int $month
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findMonth($year, $month)
    {
        $startTime = mktime(0, 0, 0, $month, 1, $year);
        $endTime = mktime(0, 0, 0, $month + 1, 1, $year) - 1;
        return $this->findByTimeSlot($startTime, $endTime);
    }

    /**
     * find Week
     *
     * @param int $year
     * @param int $week
     * @param int $weekStart See documentation for settings.weekStart
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findWeek($year, $week, $weekStart = 1)
    {
        $weekStart = (int)$weekStart;
        $daysShift = DateTimeUtility::SECONDS_DAY * ($weekStart - 1);
        $firstDay = DateTimeUtility::convertWeekYear2DayMonthYear($week, $year);
        $timeStampStart = $firstDay->getTimestamp() + $daysShift;
        return $this->findByTimeSlot($timeStampStart, $timeStampStart + DateTimeUtility::SECONDS_WEEK - 1);
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
    public function findDay($year, $month, $day)
    {
        $startTime = mktime(0, 0, 0, $month, $day, $year);
        return $this->findByTimeSlot($startTime, $startTime + DateTimeUtility::SECONDS_DAY - 1);
    }

    /**
     * Set the default sorting direction
     *
     * @param string $direction
     */
    public function setDefaultSortingDirection($direction)
    {
        $this->defaultOrderings = $this->getSorting($direction);
    }

    /**
     * Find by time slot
     *
     * @param int $startTime
     * @param int|null $endTime null means open end
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByTimeSlot($startTime, $endTime = null)
    {
        $query = $this->createQuery();
        $constraints = $this->getDefaultConstraints($query);
        $this->addTimeFrameConstraints($constraints, $query, $startTime, $endTime);
        return $this->matchAndExecute($query, $constraints);
    }

    /**
     * Find all indices by the given Event model
     *
     * @param AbstractEntity $event
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws Exception
     */
    public function findByEvent(AbstractEntity $event)
    {
        $query = $this->createQuery();
        $register = Register::getRegister();

        $uniqueRegisterKey = null;
        foreach ($register as $configuration) {
            if ($configuration['modelName'] === get_class($event)) {
                $uniqueRegisterKey = $configuration['uniqueRegisterKey'];
                break;
            }
        }

        if ($uniqueRegisterKey === null) {
            throw new Exception('No valid uniqueRegisterKey for: ' . get_class($event), 1236712);
        }

        $this->setIndexTypes([$uniqueRegisterKey]);
        $constraints = $this->getDefaultConstraints($query);
        $constraints[] = $query->equals('foreignUid', $event->getUid());
        $query->matching($query->logicalAnd($constraints));

        return $query->execute();
    }

    /**
     * storage page selection
     *
     * @return array
     */
    protected function getStoragePageIds()
    {
        if (!empty($this->overridePageIds)) {
            return $this->overridePageIds;
        }

        $configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        $frameworkConfig = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $storagePages = isset($frameworkConfig['persistence']['storagePid']) ? GeneralUtility::intExplode(
            ',',
            $frameworkConfig['persistence']['storagePid']
        ) : [];
        if (!empty($storagePages)) {
            return $storagePages;
        }
        if ($frameworkConfig instanceof BackendConfigurationManager) {
            return GeneralUtility::trimExplode(',', $frameworkConfig->getDefaultBackendStoragePid(), true);
        }
        return $storagePages;
    }

    /**
     * Get the default constraint for the queries
     *
     * @param QueryInterface $query
     *
     * @return array
     */
    protected function getDefaultConstraints(QueryInterface $query)
    {
        $constraints = [];
        if (!empty($this->indexTypes)) {
            $constraints[] = $query->in('uniqueRegisterKey', $this->indexTypes);
        }

        $storagePages = $this->getStoragePageIds();
        if (!empty($storagePages)) {
            $constraints[] = $query->in('pid', $storagePages);
        }

        $arguments = [
            'indexIds' => [],
            'indexTypes' => $this->indexTypes,
        ];
        $arguments = $this->callSignal(__CLASS__, __FUNCTION__, $arguments);

        if ($arguments['indexIds']) {
            $constraints[] = $query->in('foreign_uid', $arguments['indexIds']);
        }

        return $constraints;
    }

    /**
     * Add time frame related queries
     *
     * @param array $constraints
     * @param QueryInterface $query
     * @param int $startTime
     * @param int|null $endTime
     *
     * @see IndexUtility::isIndexInRange
     */
    protected function addTimeFrameConstraints(&$constraints, QueryInterface $query, $startTime = null, $endTime = null)
    {
        $arguments = [
            'constraints' => &$constraints,
            'query' => $query,
            'startTime' => $startTime,
            'endTime' => $endTime
        ];
        $arguments = $this->callSignal(__CLASS__, __FUNCTION__, $arguments);

        if ($arguments['startTime'] === null && $arguments['endTime'] === null) {
            return;
        } elseif ($arguments['startTime'] === null) {
            // Simulate start time
            $arguments['startTime'] = DateTimeUtility::getNow()->getTimestamp() - DateTimeUtility::SECONDS_DECADE;
        } elseif ($arguments['endTime'] === null) {
            // Simulate end time
            $arguments['endTime'] = DateTimeUtility::getNow()->getTimestamp() + DateTimeUtility::SECONDS_DECADE;
        }

        $orConstraint = [];

        // before - in
        $beforeIn = [
            $query->lessThan('start_date', $arguments['startTime']),
            $query->greaterThanOrEqual('end_date', $arguments['startTime']),
            $query->lessThan('end_date', $arguments['endTime']),
        ];
        $orConstraint[] = $query->logicalAnd($beforeIn);

        // in - in
        $inIn = [
            $query->greaterThanOrEqual('start_date', $arguments['startTime']),
            $query->lessThan('end_date', $arguments['endTime']),
        ];
        $orConstraint[] = $query->logicalAnd($inIn);

        // in - after
        $inAfter = [
            $query->greaterThanOrEqual('start_date', $arguments['startTime']),
            $query->lessThan('start_date', $arguments['endTime']),
            $query->greaterThanOrEqual('end_date', $arguments['endTime']),
        ];
        $orConstraint[] = $query->logicalAnd($inAfter);

        // before - after
        $beforeAfter = [
            $query->lessThan('start_date', $arguments['startTime']),
            $query->greaterThan('end_date', $arguments['endTime']),
        ];
        $orConstraint[] = $query->logicalAnd($beforeAfter);

        // finish
        $constraints[] = $query->logicalOr($orConstraint);
    }

    /**
     * Get the sorting
     *
     * @param string $direction
     *
     * @return array
     */
    protected function getSorting($direction)
    {
        return [
            'start_date' => $direction,
            'start_time' => $direction,
        ];
    }
}
