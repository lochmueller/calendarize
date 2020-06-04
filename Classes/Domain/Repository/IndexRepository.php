<?php

/**
 * Index repository.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

use Exception;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Model\Request\OptionRequest;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\ExtensionConfigurationUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Index repository.
 */
class IndexRepository extends AbstractRepository
{
    /**
     * Default orderings for index records.
     *
     * @var array
     */
    protected $defaultOrderings = [
        'start_date' => QueryInterface::ORDER_ASCENDING,
        'start_time' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * Index types for selection.
     *
     * @var array
     */
    protected $indexTypes = [];

    /**
     * Override page ids.
     *
     * @var array
     */
    protected $overridePageIds = [];

    /**
     * Create query.
     *
     * @return QueryInterface
     */
    public function createQuery()
    {
        $query = parent::createQuery();
        $query->getQuerySettings()->setLanguageMode($this->getIndexLanguageMode());

        return $query;
    }

    /**
     * Set the index types.
     *
     * @param array $types
     */
    public function setIndexTypes(array $types)
    {
        $this->indexTypes = $types;
    }

    /**
     * Override page IDs.
     *
     * @param array $overridePageIds
     */
    public function setOverridePageIds($overridePageIds)
    {
        $this->overridePageIds = $overridePageIds;
    }

    /**
     * Select indecies for Backend.
     *
     * @param OptionRequest $options
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllForBackend(OptionRequest $options)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setLanguageOverlayMode(false);
        $query->getQuerySettings()->setLanguageMode('ignore');

        // Notice Selection without any language handling
        unset($GLOBALS['TCA']['tx_calendarize_domain_model_index']['ctrl']['languageField'], $GLOBALS['TCA']['tx_calendarize_domain_model_index']['ctrl']['transOrigPointerField']);

        if ('asc' === $options->getDirection()) {
            $query->setOrderings([
                'start_date' => QueryInterface::ORDER_ASCENDING,
                'start_time' => QueryInterface::ORDER_ASCENDING,
            ]);
        } else {
            $query->setOrderings([
                'start_date' => QueryInterface::ORDER_DESCENDING,
                'start_time' => QueryInterface::ORDER_DESCENDING,
            ]);
        }

        if ((int)$options->getPid() > 0) {
            $query->matching($query->equals('pid', (int)$options->getPid()));
        }

        $result = $query->execute();

        return $result;
    }

    /**
     * Find List.
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
        $overrideStartDate = 0,
        $overrideEndDate = 0
    ) {
        if ($overrideStartDate > 0) {
            $startTimestamp = $overrideStartDate;
        } else {
            $now = DateTimeUtility::getNow();
            if ('now' !== $listStartTime) {
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
     * Find by custom search.
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array     $customSearch
     * @param int       $limit
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findBySearch(\DateTime $startDate = null, \DateTime $endDate = null, array $customSearch = [], int $limit = 0)
    {
        $arguments = [
            'indexIds' => [],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'customSearch' => $customSearch,
            'indexTypes' => $this->indexTypes,
            'emptyPreResult' => false,
        ];
        $arguments = $this->callSignal(__CLASS__, __FUNCTION__ . 'Pre', $arguments);

        $query = $this->createQuery();
        $constraints = $this->getDefaultConstraints($query);

        if ($limit > 0) {
            $query->setLimit($limit);
        }

        $this->addTimeFrameConstraints(
            $constraints,
            $query,
            $arguments['startDate'] instanceof \DateTime ? DateTimeUtility::getDayStart($arguments['startDate'])->getTimestamp() : null,
            $arguments['endDate'] instanceof \DateTime ? DateTimeUtility::getDayEnd($arguments['endDate'])->getTimestamp() : null
        );

        if ($arguments['indexIds']) {
            $constraints[] = $query->in('foreign_uid', $arguments['indexIds']);
        }
        if ($arguments['emptyPreResult']) {
            $constraints[] = $query->equals('uid', '-1');
        }
        $result = [
            'result' => $this->matchAndExecute($query, $constraints),
        ];

        $result = $this->callSignal(__CLASS__, __FUNCTION__ . 'Post', $result);

        return $result['result'];
    }

    /**
     * Find Past Events.
     *
     * @param int    $limit
     * @param string $sort
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByPast(
        $limit,
        $sort
    ) {
        //create Query
        $query = $this->createQuery();
        //Get actual datetime
        $now = DateTimeUtility::getNow()->getTimestamp();

        $constraints = $this->getDefaultConstraints($query);
        $constraints[] = $query->lessThanOrEqual('startDate', $now);
        $sort = QueryInterface::ORDER_ASCENDING === $sort ? QueryInterface::ORDER_ASCENDING : QueryInterface::ORDER_DESCENDING;
        $query->setOrderings($this->getSorting($sort));
        $query->setLimit($limit);

        return $this->matchAndExecute($query, $constraints);
    }

    /**
     * Find by traversing information.
     *
     * @param Index      $index
     * @param bool|true  $future
     * @param bool|false $past
     * @param int        $limit
     * @param string     $sort
     * @param bool       $useIndexTime
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
        $sort = QueryInterface::ORDER_ASCENDING === $sort ? QueryInterface::ORDER_ASCENDING : QueryInterface::ORDER_DESCENDING;
        $query->setOrderings($this->getSorting($sort));

        return $this->matchAndExecute($query, $constraints);
    }

    /**
     * Find by traversing information.
     *
     * @param DomainObjectInterface $event
     * @param bool|true             $future
     * @param bool|false            $past
     * @param int                   $limit
     * @param string                $sort
     *
     * @throws Exception
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByEventTraversing(
        DomainObjectInterface $event,
        $future = true,
        $past = false,
        $limit = 100,
        $sort = QueryInterface::ORDER_ASCENDING
    ) {
        if (!$future && !$past) {
            return [];
        }
        $query = $this->createQuery();

        $uniqueRegisterKey = ExtensionConfigurationUtility::getUniqueRegisterKeyForModel($event);

        $this->setIndexTypes([$uniqueRegisterKey]);

        $now = DateTimeUtility::getNow()
            ->getTimestamp();

        $constraints = [];

        $localizedUid = $event->_getProperty('_localizedUid');
        $selectUid = $localizedUid ? $localizedUid : $event->getUid();

        $constraints[] = $query->equals('foreignUid', $selectUid);
        $constraints[] = $query->in('uniqueRegisterKey', $this->indexTypes);
        if (!$future) {
            $constraints[] = $query->lessThanOrEqual('startDate', $now);
        }
        if (!$past) {
            $constraints[] = $query->greaterThanOrEqual('startDate', $now);
        }

        $query->setLimit($limit);
        $sort = QueryInterface::ORDER_ASCENDING === $sort ? QueryInterface::ORDER_ASCENDING : QueryInterface::ORDER_DESCENDING;
        $query->setOrderings($this->getSorting($sort));

        return $this->matchAndExecute($query, $constraints);
    }

    /**
     * find Year.
     *
     * @param int $year
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findYear(int $year)
    {
        return $this->findByTimeSlot(\mktime(0, 0, 0, 1, 1, $year), \mktime(0, 0, 0, 1, 1, $year + 1) - 1);
    }

    /**
     * find quarter.
     *
     * @param int $year
     * @param int $quarter
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findQuarter(int $year, int $quarter)
    {
        $startMonth = 1 + (3 * ($quarter - 1));

        return $this->findByTimeSlot(\mktime(0, 0, 0, $startMonth, 1, $year), \mktime(0, 0, 0, $startMonth + 3, 1, $year) - 1);
    }

    /**
     * find Month.
     *
     * @param int $year
     * @param int $month
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findMonth(int $year, int $month)
    {
        $startTime = \mktime(0, 0, 0, $month, 1, $year);
        $endTime = \mktime(0, 0, 0, $month + 1, 1, $year) - 1;

        return $this->findByTimeSlot($startTime, $endTime);
    }

    /**
     * find Week.
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
        $daysShift = $weekStart - 1;
        $firstDay = DateTimeUtility::convertWeekYear2DayMonthYear($week, $year);
        $timezone = DateTimeUtility::getTimeZone();
        $firstDay->setTimezone($timezone);
        if (0 !== $daysShift) {
            $firstDay->modify('+' . $daysShift . ' days');
        }
        $endDate = clone $firstDay;
        $endDate->modify('+1 week');
        $endDate->modify('-1 second');

        return $this->findByTimeSlot($firstDay->getTimestamp(), $endDate->getTimestamp());
    }

    /**
     * Find different types and locations.
     *
     * @return array
     */
    public function findDifferentTypesAndLocations(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_calendarize_domain_model_index');

        return (array)$queryBuilder->select('unique_register_key', 'pid', 'foreign_table')->from('tx_calendarize_domain_model_index')->groupBy('pid', 'foreign_table', 'unique_register_key')->execute()->fetchAll();
    }

    /**
     * find day.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     *
     * @throws Exception
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findDay(int $year, int $month, int $day)
    {
        $startTime = \mktime(0, 0, 0, $month, $day, $year);
        $startDate = new \DateTime('@' . $startTime);
        $endDate = clone $startDate;
        $endDate->modify('+1 day');
        $endDate->modify('-1 second');

        return $this->findByTimeSlot($startDate->getTimestamp(), $endDate->getTimestamp());
    }

    /**
     * Set the default sorting direction.
     *
     * @param string $direction
     * @param string $field
     */
    public function setDefaultSortingDirection($direction, $field = '')
    {
        $this->defaultOrderings = $this->getSorting($direction, $field);
    }

    /**
     * Find by time slot.
     *
     * @param int      $startTime
     * @param int|null $endTime   null means open end
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
     * Find all indices by the given Event model.
     *
     * @param DomainObjectInterface $event
     *
     * @throws Exception
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByEvent(DomainObjectInterface $event)
    {
        $query = $this->createQuery();

        $uniqueRegisterKey = ExtensionConfigurationUtility::getUniqueRegisterKeyForModel($event);

        $this->setIndexTypes([$uniqueRegisterKey]);
        $constraints = $this->getDefaultConstraints($query);
        $constraints[] = $query->equals('foreignUid', $event->getUid());
        $query->matching($query->logicalAnd($constraints));

        return $query->execute();
    }

    /**
     * Get index language mode.
     *
     * @return string
     */
    protected function getIndexLanguageMode()
    {
        static $mode;
        if (null !== $mode) {
            return $mode;
        }

        $objectManager = new ObjectManager();
        /** @var ConfigurationManagerInterface $config */
        $config = $objectManager->get(ConfigurationManagerInterface::class);
        $pluginConfig = $config->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);

        $mode = isset($pluginConfig['indexLanguageMode']) ? (string)$pluginConfig['indexLanguageMode'] : 'strict';

        return $mode;
    }

    /**
     * storage page selection.
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
     * Get the default constraint for the queries.
     *
     * @param QueryInterface $query
     *
     * @return array
     */
    protected function getDefaultConstraints(QueryInterface $query)
    {
        $constraints = [];
        if (!empty($this->indexTypes)) {
            $indexTypes = $this->indexTypes;
            $constraints[] = $query->in('uniqueRegisterKey', $indexTypes);
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
     * Add time frame related queries.
     *
     * @param array          $constraints
     * @param QueryInterface $query
     * @param int            $startTime
     * @param int|null       $endTime
     *
     * @see IndexUtility::isIndexInRange
     */
    protected function addTimeFrameConstraints(&$constraints, QueryInterface $query, $startTime = null, $endTime = null)
    {
        $arguments = [
            'constraints' => &$constraints,
            'query' => $query,
            'startTime' => $startTime,
            'endTime' => $endTime,
        ];
        $arguments = $this->callSignal(__CLASS__, __FUNCTION__, $arguments);

        if (null === $arguments['startTime'] && null === $arguments['endTime']) {
            return;
        }
        if (null === $arguments['startTime']) {
            // Simulate start time
            $arguments['startTime'] = DateTimeUtility::getNow()->getTimestamp() - DateTimeUtility::SECONDS_DECADE;
        } elseif (null === $arguments['endTime']) {
            // Simulate end time
            $arguments['endTime'] = DateTimeUtility::getNow()->getTimestamp() + DateTimeUtility::SECONDS_DECADE;
        }

        if ((bool)ConfigurationUtility::get('respectTimesInTimeFrameConstraints')) {
            $this->addDateTimeFrameConstraints($constraints, $query, $arguments);
        } else {
            $this->addDateFrameConstraints($constraints, $query, $arguments);
        }
    }

    /**
     * Adds time frame constraints which respect the actual index times.
     * Do not call this method directly. Call IndexRepository::addTimeFrameConstraints instead.
     *
     * @param array          $constraints
     * @param QueryInterface $query
     * @param array          $arguments
     *
     * @see IndexRepository::addTimeFrameConstraints
     */
    protected function addDateTimeFrameConstraints(&$constraints, QueryInterface $query, array $arguments)
    {
        $timezone = new \DateTimeZone('UTC');

        // store values for start_date and start_time in separate variables
        $startDateTime = new \DateTime('@' . $arguments['startTime'], $timezone);
        $restrictionLowTime = DateTimeUtility::getDaySecondsOfDateTime($startDateTime);
        $restrictionLowDay = DateTimeUtility::resetTime($startDateTime)->format('Y-m-d');

        // store values for end_date and end_time in separate variables
        $endDateTime = new \DateTime('@' . $arguments['endTime'], $timezone);
        $restrictionHighTime = DateTimeUtility::getDaySecondsOfDateTime($endDateTime);
        $restrictionHighDay = DateTimeUtility::resetTime($endDateTime)->format('Y-m-d');

        $constraints[] = $query->logicalAnd([
            // (end_date === restrictionLowDay && end_time >= restrictionLowTime) || end_date > restrictionLowDay || (all_day === true && end_date >= restrictionLowDay)
            $query->logicalOr([
                $query->logicalAnd([
                    $query->equals('end_date', $query->createNamedParameter($restrictionLowDay)),
                    $query->greaterThanOrEqual('end_time', $restrictionLowTime),
                ]),
                $query->greaterThan('end_date', $query->createNamedParameter($restrictionLowDay)),
                $query->logicalAnd([
                    $query->equals('all_day', true),
                    $query->greaterThanOrEqual('end_date', $query->createNamedParameter($restrictionLowDay)),
                ]),
            ]),
            // (start_date === restrictionHighDay && start_time <= restrictionHighTime) || start_date < restrictionHighDay || (all_day === true && start_date <= restrictionHighDay)
            $query->logicalOr([
                $query->logicalAnd([
                    $query->equals('start_date', $query->createNamedParameter($restrictionHighDay)),
                    $query->lessThanOrEqual('start_time', $restrictionHighTime),
                ]),
                $query->lessThan('start_date', $query->createNamedParameter($restrictionHighDay)),
                $query->logicalAnd([
                    $query->equals('all_day', true),
                    $query->lessThanOrEqual('start_date', $query->createNamedParameter($restrictionHighDay)),
                ]),
            ]),
        ]);
    }

    /**
     * Adds time frame constraints which respect only the index dates, not the actual index times.
     * Do not call this method directly. Call IndexRepository::addTimeFrameConstraints instead.
     *
     * @param array          $constraints
     * @param QueryInterface $query
     * @param array          $arguments
     *
     * @see IndexRepository::addTimeFrameConstraints
     */
    protected function addDateFrameConstraints(&$constraints, QueryInterface $query, array $arguments)
    {
        $orConstraint = [];

        // before - in
        $beforeIn = [
            $query->lessThan('start_date', (new \DateTime('@' . $arguments['startTime']))->format('Y-m-d')),
            $query->greaterThanOrEqual('end_date', (new \DateTime('@' . $arguments['endTime']))->format('Y-m-d')),
            $query->lessThan('end_date', (new \DateTime('@' . $arguments['endTime']))->format('Y-m-d')),
        ];
        $orConstraint[] = $query->logicalAnd($beforeIn);

        // in - in
        $inIn = [
            $query->greaterThanOrEqual('start_date', (new \DateTime('@' . $arguments['startTime']))->format('Y-m-d')),
            $query->lessThan('end_date', (new \DateTime('@' . $arguments['endTime']))->format('Y-m-d')),
        ];
        $orConstraint[] = $query->logicalAnd($inIn);

        // in - after
        $inAfter = [
            $query->greaterThanOrEqual('start_date', (new \DateTime('@' . $arguments['startTime']))->format('Y-m-d')),
            $query->lessThan('start_date', (new \DateTime('@' . $arguments['endTime']))->format('Y-m-d')),
            $query->greaterThanOrEqual('end_date', (new \DateTime('@' . $arguments['endTime']))->format('Y-m-d')),
        ];
        $orConstraint[] = $query->logicalAnd($inAfter);

        // before - after
        $beforeAfter = [
            $query->lessThan('start_date', (new \DateTime('@' . $arguments['startTime']))->format('Y-m-d')),
            $query->greaterThan('end_date', (new \DateTime('@' . $arguments['endTime']))->format('Y-m-d')),
        ];
        $orConstraint[] = $query->logicalAnd($beforeAfter);

        // finish
        $constraints[] = $query->logicalOr($orConstraint);
    }

    /**
     * Get the sorting.
     *
     * @param string $direction
     * @param string $field
     *
     * @return array
     */
    protected function getSorting($direction, $field = '')
    {
        if ('withrangelast' === $field) {
            return [
                'end_date' => $direction,
                'start_date' => $direction,
                'start_time' => $direction,
            ];
        }
        if ('end' !== $field) {
            $field = 'start';
        }

        return [
            $field . '_date' => $direction,
            $field . '_time' => $direction,
        ];
    }
}
