<?php

/**
 * Index repository.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

use Exception;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Model\Request\OptionRequest;
use HDNET\Calendarize\Event\AddTimeFrameConstraintsEvent;
use HDNET\Calendarize\Event\IndexRepositoryDefaultConstraintEvent;
use HDNET\Calendarize\Event\IndexRepositoryFindBySearchEvent;
use HDNET\Calendarize\Event\IndexRepositoryTimeSlotEvent;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\ExtensionConfigurationUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
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
    protected $overridePageIds;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Create query.
     *
     * @return QueryInterface
     */
    public function createQuery()
    {
        $query = parent::createQuery();

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
     * @param array         $allowedPages
     * @param bool          $ignoreEnableFields
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllForBackend(OptionRequest $options, array $allowedPages = [], bool $ignoreEnableFields = true)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields($ignoreEnableFields);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setLanguageOverlayMode(false);

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

        $constraints = [];

        if ((int)$options->getPid() > 0) {
            $constraints[] = $query->equals('pid', (int)$options->getPid());
        } elseif ($allowedPages) {
            $constraints[] = $query->in('pid', $allowedPages);
        }

        if ('' !== $options->getType()) {
            $constraints[] = $query->equals('uniqueRegisterKey', $options->getType());
        }

        $this->addDateTimeFrameConstraint(
            $constraints,
            $query,
            $options->getStartDate(),
            $options->getEndDate()
        );

        if ($constraints) {
            $query->matching($query->logicalAnd($constraints));
        }

        return $query->execute();
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
        $startTime = DateTimeUtility::getNow();
        $endTime = null;

        if ($overrideStartDate > 0) {
            // Note: setTimestamp does not change the timezone
            $startTime->setTimestamp($overrideStartDate);
        } else {
            if ('now' !== $listStartTime) {
                $startTime->setTime(0, 0, 0);
            }
            $startTime->modify($startOffsetHours . ' hours');
        }

        if ($overrideEndDate > 0) {
            $endTime = DateTimeUtility::getNow()->setTimestamp($overrideEndDate);
        }

        $result = $this->findByTimeSlot($startTime, $endTime);
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
     * @param \DateTimeInterface|null $startDate
     * @param \DateTimeInterface|null $endDate
     * @param array                   $customSearch
     * @param int                     $limit
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findBySearch(
        \DateTimeInterface $startDate = null,
        \DateTimeInterface $endDate = null,
        array $customSearch = [],
        int $limit = 0
    ) {
        $event = new IndexRepositoryFindBySearchEvent([], $startDate, $endDate, $customSearch, $this->indexTypes, false);
        $this->eventDispatcher->dispatch($event);

        $query = $this->createQuery();
        $constraints = $this->getDefaultConstraints($query);

        if ($limit > 0) {
            $query->setLimit($limit);
        }

        $this->addTimeFrameConstraints(
            $constraints,
            $query,
            $event->getStartDate(),
            $event->getEndDate()
        );

        if ($event->getIndexIds()) {
            $indexIds = [];
            $tabledIndexIds = [];
            foreach ($event->getIndexIds() as $key => $indexId) {
                if (\is_int($key)) {
                    // Plain integers (= deprecated old way, stays in for compatibility)
                    $indexIds[] = $indexId;
                } elseif (\is_string($key) && \is_array($indexId)) {
                    // Table based values with array of foreign uids
                    $tabledIndexIds[] = [
                        'table' => $key,
                        'indexIds' => $indexId,
                    ];
                } elseif (\is_string($key) && \is_int($indexId)) {
                    // Table based single return value
                    $tabledIndexIds[] = [
                         'table' => $key,
                         'indexIds' => [$indexId],
                    ];
                }
            }
            $foreignIdConstraints = [];
            // Old way, just accept foreignUids as provided, not checking the table.
            // This has a caveat solved with the $tabledIndexIds
            if ($indexIds) {
                $foreignIdConstraints[] = $query->in('foreignUid', $indexIds);
            }
            if ($tabledIndexIds) {
                // Handle each table individually on the filters
                // allowing for uids to be table specific.
                // If 1,3,5 on table_a are ok and 4,5,7 on table_b are ok,
                // don't show uid 1 from table_b
                foreach ($tabledIndexIds as $tabledIndexId) {
                    if ($tabledIndexId['indexIds']) {
                        // This table has used filters and returned some allowed uids.
                        // Providing non-existing values e.g.: -1 will remove everything
                        // unless other elements have found elements with the filters
                        $foreignIdConstraints[] = $query->logicalAnd([
                            $query->equals('foreignTable', $tabledIndexId['table']),
                            $query->in('foreignUid', $tabledIndexId['indexIds']),
                        ]);
                    }
                }
            }
            if (\count($foreignIdConstraints) > 1) {
                // Multiple valid tables should be grouped by "OR"
                // so it's either table_a with uids 1,3,4 OR table_b with uids 1,5,7
                $foreignIdConstraint = $query->logicalOr($foreignIdConstraints);
            } else {
                // Single constraint or no constraint should just be simply added
                $foreignIdConstraint = array_shift($foreignIdConstraints);
            }
            // If any foreignUid constraint survived, use it on the query
            if ($foreignIdConstraint) {
                $constraints[] = $foreignIdConstraint;
            }
        }
        if ($event->isEmptyPreResult()) {
            $constraints[] = $query->equals('uid', '-1');
        }

        return $this->matchAndExecute($query, $constraints);
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
        $sort,
        $listStartTime = 0
    ) {
        $now = DateTimeUtility::getNow();
        if ('now' !== $listStartTime) {
            $now->setTime(0, 0, 0);
        }

        $query = $this->createQuery();

        $constraints = $this->getDefaultConstraints($query);
        $this->addTimeFrameConstraints($constraints, $query, null, $now);

        $sort = QueryInterface::ORDER_ASCENDING === $sort ? QueryInterface::ORDER_ASCENDING : QueryInterface::ORDER_DESCENDING;
        $query->setOrderings($this->getSorting($sort));
        if ($limit > 0) {
            $query->setLimit($limit);
        }

        return $this->matchAndExecute($query, $constraints);
    }

    /**
     * Find by Index UIDs.
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByUids(
        array $uids
    ) {
        $query = $this->createQuery();
        $query->setOrderings($this->getSorting(QueryInterface::ORDER_ASCENDING));
        $constraints = [
            $query->in('uid', $uids),
        ];

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

        $now = DateTimeUtility::getNow();
        if ($useIndexTime) {
            $now = $index->getStartDate();
        }

        $constraints = [];
        $constraints[] = $query->logicalNot($query->equals('uid', $index->getUid()));
        $constraints[] = $query->equals('foreignTable', $index->getForeignTable());
        $constraints[] = $query->equals('foreignUid', $index->getForeignUid());
        if (!$future) {
            $constraints[] = $query->lessThanOrEqual('startDate', $now->format('Y-m-d'));
        }
        if (!$past) {
            $constraints[] = $query->greaterThanOrEqual('startDate', $now->format('Y-m-d'));
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

        $now = DateTimeUtility::getNow()->format('Y-m-d');

        $constraints = [];

        $localizedUid = $event->_getProperty('_localizedUid');
        $selectUid = $localizedUid ?: $event->getUid();

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
        $startTime = (new \DateTimeImmutable('midnight'))->setDate($year, 1, 1);
        $endTime = $startTime->modify('+1 year -1 second');

        return $this->findByTimeSlot($startTime, $endTime);
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
        $startTime = (new \DateTimeImmutable('midnight'))->setDate($year, $startMonth, 1);
        $endTime = $startTime->modify('+3 months -1 second');

        return $this->findByTimeSlot($startTime, $endTime);
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
        $startTime = (new \DateTimeImmutable('midnight'))->setDate($year, $month, 1);
        $endTime = $startTime->modify('+1 month -1 second');

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
    public function findWeek(int $year, int $week, int $weekStart = 1)
    {
        $startTime = \DateTimeImmutable::createFromMutable(DateTimeUtility::convertWeekYear2DayMonthYear($week, $year, $weekStart));
        $endTime = $startTime->modify('+1 week -1 second');

        return $this->findByTimeSlot($startTime, $endTime);
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
        $startTime = (new \DateTimeImmutable('midnight'))->setDate($year, $month, $day);
        $endTime = $startTime->modify('+1 day -1 second');

        return $this->findByTimeSlot($startTime, $endTime);
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
     * @param \DateTimeInterface|null $startTime
     * @param \DateTimeInterface|null $endTime
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByTimeSlot(?\DateTimeInterface $startTime, ?\DateTimeInterface $endTime = null)
    {
        $query = $this->createQuery();
        $constraints = $this->getDefaultConstraints($query);
        $this->addTimeFrameConstraints($constraints, $query, $startTime, $endTime);

        $event = new IndexRepositoryTimeSlotEvent($constraints, $query);
        $this->eventDispatcher->dispatch($event);

        return $this->matchAndExecute($query, $event->getConstraints());
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
     * storage page selection.
     *
     * @return array
     */
    protected function getStoragePageIds()
    {
        if (null !== $this->overridePageIds) {
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

        $event = new IndexRepositoryDefaultConstraintEvent([], $this->indexTypes, $this->additionalSlotArguments);
        $this->eventDispatcher->dispatch($event);

        if ($event->getIndexIds()) {
            $constraints[] = $query->in('foreignUid', $event->getIndexIds());
        }

        return $constraints;
    }

    /**
     * Add time frame related queries.
     *
     * @param array                   $constraints
     * @param QueryInterface          $query
     * @param \DateTimeInterface|null $startTime
     * @param \DateTimeInterface|null $endTime
     *
     * @see IndexUtility::isIndexInRange
     */
    protected function addTimeFrameConstraints(
        array &$constraints,
        QueryInterface $query,
        ?\DateTimeInterface $startTime = null,
        ?\DateTimeInterface $endTime = null
    ): void {
        /** @var AddTimeFrameConstraintsEvent $event */
        $event = $this->eventDispatcher->dispatch(new AddTimeFrameConstraintsEvent(
            $constraints,
            $query,
            $this->additionalSlotArguments,
            $startTime,
            $endTime
        ));

        $this->addDateTimeFrameConstraint(
            $constraints,
            $query,
            $event->getStart(),
            $event->getEnd(),
            (bool)ConfigurationUtility::get('respectTimesInTimeFrameConstraints')
        );
    }

    /**
     * Adds time frame constraints. The dates are formatted the timezone of the DateTime objects.
     * This includes all events, that have an "active" part in the range.
     * Do not call this method directly. Call IndexRepository::addTimeFrameConstraints instead.
     *
     * @param array                   $constraints
     * @param QueryInterface          $query
     * @param \DateTimeInterface|null $start
     * @param \DateTimeInterface|null $end         Inclusive end date
     * @param bool                    $respectTime if true, it will also respect the time of the indices
     *
     * @see IndexRepository::addTimeFrameConstraints
     */
    protected function addDateTimeFrameConstraint(
        array &$constraints,
        QueryInterface $query,
        ?\DateTimeInterface $start,
        ?\DateTimeInterface $end,
        bool $respectTime = false
    ): void {
        if (null === $start && null === $end) {
            return;
        }
        $dateConstraints = [];

        // No start means open end
        if (null !== $start) {
            $startDate = $start->format('Y-m-d');

            if (false === $respectTime) {
                // The endDate of an index must be after the range start, otherwise the event was in the past
                $dateConstraints[] = $query->greaterThanOrEqual('endDate', $startDate);
            } else {
                $startTime = DateTimeUtility::getDaySecondsOfDateTime($start);

                // We split up greaterThan and equal to check more conditions on the same day
                // e.g. if it is either allDay, openEnd or the end time is after the start time
                // (endDate > $startDate) || (endDate == $startDate && (allDay || openEndTime || endTime >= $startTime))
                $dateConstraints[] = $query->logicalOr([
                    $query->greaterThan('endDate', $startDate),
                    $query->logicalAnd([
                        $query->equals('endDate', $startDate),
                        $query->logicalOr([
                            $query->equals('allDay', true),
                            $query->equals('openEndTime', true),
                            $query->greaterThanOrEqual('endTime', $startTime),
                        ]),
                    ]),
                ]);
            }
        }

        // No end means open start
        if (null !== $end) {
            $endDate = $end->format('Y-m-d');

            if (false === $respectTime) {
                // The startDate of an index must be before the range end, otherwise the event is in the future
                $dateConstraints[] = $query->lessThanOrEqual('startDate', $endDate);
            } else {
                $endTime = DateTimeUtility::getDaySecondsOfDateTime($end);

                $dateConstraints[] = $query->logicalOr([
                    $query->lessThan('startDate', $endDate),
                    $query->logicalAnd([
                        $query->equals('startDate', $endDate),
                        $query->logicalOr([
                            $query->equals('allDay', true),
                            $query->lessThanOrEqual('startTime', $endTime),
                        ]),
                    ]),
                ]);
            }
        }

        $constraints['dateTimeFrame'] = $query->logicalAnd($dateConstraints);
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
                'endDate' => $direction,
                'startDate' => $direction,
                'startTime' => $direction,
            ];
        }
        if ('end' !== $field) {
            $field = 'start';
        }

        return [
            $field . 'Date' => $direction,
            $field . 'Time' => $direction,
        ];
    }
}
