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
use HDNET\Calendarize\Utility\HelperUtility;
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
     * Current content record
     *
     * @var array
     */
    protected $contentRecord = [];

    /**
     * Override page ids
     *
     * @var array
     */
    protected $overridePageIds = [];

    /**
     * Set the current content record
     *
     * @param array $contentRecord
     */
    public function setContentRecord($contentRecord)
    {
        $this->contentRecord = $contentRecord;
    }

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
     * @param int        $limit
     * @param int|string $listStartTime
     * @param int        $startOffsetHours
     * @param int        $overrideStartDate
     * @param int        $overrideEndDate
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findList(
        $limit = 0,
        $listStartTime = 0,
        $startOffsetHours = 0,
        $overrideStartDate = null,
        $overrideEndDate = null
    ) {
        if ($overrideStartDate) {
            $startTimestamp = $overrideStartDate;
        } else {
            $now = DateTimeUtility::getNow();
            if ($listStartTime !== 'now') {
                $now->setTime(0, 0, 0);
            }
            $now->modify($startOffsetHours . ' hours');
            $startTimestamp = $now->getTimestamp();
        }
        $endTimestamp = null;
        if ($overrideEndDate) {
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
     * @param array     $customSearch
     *
     * @return array
     */
    public function findBySearch(\DateTime $startDate = null, \DateTime $endDate = null, array $customSearch = [])
    {
        $arguments = [
            'indexIds'     => [],
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'customSearch' => $customSearch,
        ];
        $signalSlotDispatcher = HelperUtility::getSignalSlotDispatcher();
        $arguments = $signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'Pre', $arguments);

        $query = $this->createQuery();
        $constraints = $this->getDefaultConstraints($query);
        if ($arguments['startDate'] instanceof \DateTime) {
            $constraints[] = $query->greaterThan('start_date', $arguments['startDate']);
        }
        if ($arguments['endDate'] instanceof \DateTime) {
            $constraints[] = $query->lessThan('start_date', $arguments['endDate']);
        }
        if ($arguments['indexIds']) {
            $constraints[] = $query->in('foreign_uid', $arguments['indexIds']);
        }
        $result = [
            'result' => $this->matchAndExecute($query, $constraints)
        ];
        $signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'Post', $result);

        return $result['result'];
    }

    /**
     * Find by traversing information
     *
     * @param Index      $index
     * @param bool|true  $future
     * @param bool|false $past
     * @param int        $limit
     * @param string     $sort
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByTraversing(
        Index $index,
        $future = true,
        $past = false,
        $limit = 100,
        $sort = QueryInterface::ORDER_ASCENDING
    ) {
        if (!$future && !$past) {
            return [];
        }
        $query = $this->createQuery();
        $now = DateTimeUtility::getNow()
            ->getTimestamp();
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
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findWeek($year, $week)
    {
        $firstDay = DateTimeUtility::convertWeekYear2DayMonthYear($week, $year);
        $timeStampStart = $firstDay->getTimestamp();
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
     * @param int      $startTime
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
            throw new Exception('No valid uniqueRegisterKey for: ' . get_class($event), 1236712342);
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

        /** @var ConfigurationManagerInterface $configuratioManager */
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
        if ($this->indexTypes) {
            $constraints[] = $query->in('uniqueRegisterKey', $this->indexTypes);
        }

        $storagePages = $this->getStoragePageIds();
        if (!empty($storagePages)) {
            $constraints[] = $query->in('pid', $storagePages);
        }

        $arguments = [
            'indexIds'      => [],
            'indexTypes'    => $this->indexTypes,
            'contentRecord' => $this->contentRecord,
        ];
        $signalSlotDispatcher = HelperUtility::getSignalSlotDispatcher();
        $arguments = $signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__, $arguments);

        if ($arguments['indexIds']) {
            $constraints[] = $query->in('foreign_uid', $arguments['indexIds']);
        }

        return $constraints;
    }

    /**
     * Add time frame related queries
     *
     * @param array          $constraints
     * @param QueryInterface $query
     * @param int            $startTime
     * @param int|null       $endTime
     *
     * @see IndexUtility::isIndexInRange
     */
    protected function addTimeFrameConstraints(&$constraints, QueryInterface $query, $startTime, $endTime = null)
    {
        // Simulate end time
        if ($endTime === null) {
            $endTime = $startTime + DateTimeUtility::SECONDS_DECADE;
        }
        $orConstraint = [];

        // before - in
        $beforeIn = [
            $query->lessThan('start_date', $startTime),
            $query->greaterThanOrEqual('end_date', $startTime),
            $query->lessThan('end_date', $endTime),
        ];
        $orConstraint[] = $query->logicalAnd($beforeIn);

        // in - in
        $inIn = [
            $query->greaterThanOrEqual('start_date', $startTime),
            $query->lessThan('end_date', $endTime),
        ];
        $orConstraint[] = $query->logicalAnd($inIn);

        // in - after
        $inAfter = [
            $query->greaterThanOrEqual('start_date', $startTime),
            $query->lessThan('start_date', $endTime),
            $query->greaterThanOrEqual('end_date', $endTime),
        ];
        $orConstraint[] = $query->logicalAnd($inAfter);

        // before - after
        $beforeAfter = [
            $query->lessThan('start_date', $startTime),
            $query->greaterThan('end_date', $endTime),
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
