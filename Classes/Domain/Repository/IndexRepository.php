<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Model\Request\OptionRequest;
use HDNET\Calendarize\Event\AddTimeFrameConstraintsEvent;
use HDNET\Calendarize\Event\IndexRepositoryDefaultConstraintEvent;
use HDNET\Calendarize\Event\IndexRepositoryFindBySearchEvent;
use HDNET\Calendarize\Event\IndexRepositoryTimeSlotEvent;
use HDNET\Calendarize\Event\ModifyDateTimeFrameConstraintEvent;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\ExtensionConfigurationUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

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
        'uid' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * Index types for selection.
     */
    protected array $indexTypes = [];

    /**
     * Override page ids.
     */
    protected ?array $overridePageIds = null;

    protected EventDispatcherInterface $eventDispatcher;

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Set the index types.
     */
    public function setIndexTypes(array $types): void
    {
        $this->indexTypes = $types;
    }

    /**
     * Override page IDs.
     */
    public function setOverridePageIds(?array $overridePageIds): void
    {
        $this->overridePageIds = $overridePageIds;
    }

    /**
     * Select indices for Backend.
     */
    public function findAllForBackend(
        OptionRequest $options,
        array $allowedPages = [],
        bool $ignoreEnableFields = true,
    ): array|QueryResultInterface {
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings();
        $querySettings->setIgnoreEnableFields($ignoreEnableFields);
        $querySettings->setRespectSysLanguage(false);
        $querySettings->setLanguageAspect(new LanguageAspect(
            $querySettings->getLanguageAspect()->getId(),
            $querySettings->getLanguageAspect()->getContentId(),
            LanguageAspect::OVERLAYS_OFF,
        ));

        // Notice Selection without any language handling
        unset(
            $GLOBALS['TCA']['tx_calendarize_domain_model_index']['ctrl']['languageField'],
            $GLOBALS['TCA']['tx_calendarize_domain_model_index']['ctrl']['transOrigPointerField'],
        );

        if ('asc' === $options->getDirection()) {
            $query->setOrderings([
                'start_date' => QueryInterface::ORDER_ASCENDING,
                'start_time' => QueryInterface::ORDER_ASCENDING,
                'uid' => QueryInterface::ORDER_ASCENDING,
            ]);
        } else {
            $query->setOrderings([
                'start_date' => QueryInterface::ORDER_DESCENDING,
                'start_time' => QueryInterface::ORDER_DESCENDING,
                'uid' => QueryInterface::ORDER_DESCENDING,
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
            $options->getEndDate(),
        );

        if ($constraints) {
            $query->matching($query->logicalAnd(...$constraints));
        }

        return $query->execute();
    }

    /**
     * Find List.
     */
    public function findList(
        int $limit = 0,
        int|string $listStartTime = 0,
        int $startOffsetHours = 0,
        int $overrideStartDate = 0,
        int $overrideEndDate = 0,
        bool $ignoreStoragePid = false,
    ): array|QueryResultInterface {
        $startTime = DateTimeUtility::getNow();
        $endTime = null;

        if ($overrideStartDate > 0) {
            // Note: setTimestamp does not change the timezone
            $startTime->setTimestamp($overrideStartDate);
        } else {
            if ('now' !== $listStartTime) {
                $startTime->setTime(0, 0);
            }
            $startTime->modify($startOffsetHours . ' hours');
        }

        if ($overrideEndDate > 0) {
            $endTime = DateTimeUtility::getNow()->setTimestamp($overrideEndDate);
        }

        if ($ignoreStoragePid) {
            $this->overridePageIds = [];
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
     */
    public function findBySearch(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
        array $customSearch = [],
        int $limit = 0,
    ): array|QueryResultInterface {
        $event = $this->eventDispatcher->dispatch(new IndexRepositoryFindBySearchEvent(
            $startDate,
            $endDate,
            $customSearch,
            $this->indexTypes,
            false,
        ));

        $query = $this->createQuery();
        $constraints = $this->getDefaultConstraints($query);

        if ($limit > 0) {
            $query->setLimit($limit);
        }

        $this->addTimeFrameConstraints(
            $constraints,
            $query,
            $event->getStartDate(),
            $event->getEndDate(),
        );

        if ($event->getForeignIds()) {
            $constraints[] = $this->addForeignIdConstraints($query, $event->getForeignIds());
        }
        if ($event->isEmptyPreResult()) {
            $constraints[] = $query->equals('uid', '-1');
        }

        return $this->matchAndExecute($query, $constraints);
    }

    /**
     * Find Past Events.
     */
    public function findByPast(int $limit, string $sort, string $listStartTime = '0'): array|QueryResultInterface
    {
        $now = DateTimeUtility::getNow();
        if ('now' !== $listStartTime) {
            $now->setTime(0, 0);
        }

        $query = $this->createQuery();

        $constraints = $this->getDefaultConstraints($query);
        $this->addTimeFrameConstraints($constraints, $query, null, $now);

        $sort = QueryInterface::ORDER_ASCENDING === $sort
            ? QueryInterface::ORDER_ASCENDING
            : QueryInterface::ORDER_DESCENDING;
        $query->setOrderings($this->getSorting($sort));
        if ($limit > 0) {
            $query->setLimit($limit);
        }

        return $this->matchAndExecute($query, $constraints);
    }

    /**
     * Find by Index UIDs.
     */
    public function findByUids(array $uids): array|QueryResultInterface
    {
        $query = $this->createQuery();
        $query->setOrderings($this->getSorting(QueryInterface::ORDER_ASCENDING));
        $constraints = [
            $query->in('uid', $uids),
        ];

        return $this->matchAndExecute($query, $constraints);
    }

    /**
     * Find by traversing information.
     */
    public function findByTraversing(
        Index $index,
        bool $future = true,
        bool $past = false,
        int $limit = 100,
        string $sort = QueryInterface::ORDER_ASCENDING,
        bool $useIndexTime = false,
    ): array|QueryResultInterface {
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
        $sort = QueryInterface::ORDER_ASCENDING === $sort ?
            QueryInterface::ORDER_ASCENDING :
            QueryInterface::ORDER_DESCENDING;
        $query->setOrderings($this->getSorting($sort));

        return $this->matchAndExecute($query, $constraints);
    }

    public function findByTableAndUid(
        string $table,
        int $uid,
        bool $future = true,
        bool $past = false,
        int $limit = 100,
        string $sort = QueryInterface::ORDER_ASCENDING,
        ?\DateTimeImmutable $referenceDate = null,
    ): array|QueryResultInterface {
        if (!$future && !$past) {
            return [];
        }
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setRespectSysLanguage(false);

        $constraints[] = $query->equals('foreignUid', $uid);
        $constraints[] = $query->equals('foreignTable', $table);

        $now = ($referenceDate ?? DateTimeUtility::getNow())->format('Y-m-d');
        if (!$future) {
            $constraints[] = $query->lessThanOrEqual('startDate', $now);
        }
        if (!$past) {
            $constraints[] = $query->greaterThanOrEqual('startDate', $now);
        }

        if ($limit > 0) {
            $query->setLimit($limit);
        }
        $sort = QueryInterface::ORDER_ASCENDING === $sort ?
            QueryInterface::ORDER_ASCENDING :
            QueryInterface::ORDER_DESCENDING;
        $query->setOrderings($this->getSorting($sort));

        return $this->matchAndExecute($query, $constraints);
    }

    /**
     * Find by traversing information.
     */
    public function findByEventTraversing(
        DomainObjectInterface $event,
        bool $future = true,
        bool $past = false,
        int $limit = 100,
        string $sort = QueryInterface::ORDER_ASCENDING,
    ): array|QueryResultInterface {
        if (!$future && !$past) {
            return [];
        }
        $tableName = ExtensionConfigurationUtility::getConfigurationForModel($event)['tableName'];

        $localizedUid = $event->_getProperty('_localizedUid');
        $selectUid = $localizedUid ?: $event->getUid();

        return $this->findByTableAndUid(
            $tableName,
            $selectUid,
            $future,
            $past,
            $limit,
            $sort,
        );
    }

    /**
     * find Year.
     */
    public function findYear(int $year): array|QueryResultInterface
    {
        $startTime = (new \DateTimeImmutable('midnight'))->setDate($year, 1, 1);
        $endTime = $startTime->modify('+1 year -1 second');

        return $this->findByTimeSlot($startTime, $endTime);
    }

    /**
     * find quarter.
     */
    public function findQuarter(int $year, int $quarter): array|QueryResultInterface
    {
        $startMonth = 1 + (3 * ($quarter - 1));
        $startTime = (new \DateTimeImmutable('midnight'))->setDate($year, $startMonth, 1);
        $endTime = $startTime->modify('+3 months -1 second');

        return $this->findByTimeSlot($startTime, $endTime);
    }

    /**
     * find Month.
     */
    public function findMonth(int $year, int $month): array|QueryResultInterface
    {
        $startTime = (new \DateTimeImmutable('midnight'))->setDate($year, $month, 1);
        $endTime = $startTime->modify('+1 month -1 second');

        return $this->findByTimeSlot($startTime, $endTime);
    }

    /**
     * find Week.
     */
    public function findWeek(int $year, int $week, int $weekStart = 1): array|QueryResultInterface
    {
        $startTime = \DateTimeImmutable::createFromMutable(
            DateTimeUtility::convertWeekYear2DayMonthYear($week, $year, $weekStart),
        );
        $endTime = $startTime->modify('+1 week -1 second');

        return $this->findByTimeSlot($startTime, $endTime);
    }

    /**
     * find day.
     */
    public function findDay(int $year, int $month, int $day): array|QueryResultInterface
    {
        $startTime = (new \DateTimeImmutable('midnight'))->setDate($year, $month, $day);
        $endTime = $startTime->modify('+1 day -1 second');

        return $this->findByTimeSlot($startTime, $endTime);
    }

    /**
     * Find different types and locations.
     */
    public function findDifferentTypesAndLocations(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_calendarize_domain_model_index');

        return (array)$queryBuilder
            ->select('unique_register_key', 'pid', 'foreign_table')
            ->from('tx_calendarize_domain_model_index')
            ->groupBy('pid', 'foreign_table', 'unique_register_key')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * Set the default sorting direction.
     */
    public function setDefaultSortingDirection(string $direction, string $field = ''): void
    {
        $this->defaultOrderings = $this->getSorting($direction, $field);
    }

    /**
     * Find by time slot.
     */
    public function findByTimeSlot(
        ?\DateTimeInterface $startTime,
        ?\DateTimeInterface $endTime = null,
    ): array|QueryResultInterface {
        $query = $this->createQuery();
        $constraints = $this->getDefaultConstraints($query);
        $this->addTimeFrameConstraints($constraints, $query, $startTime, $endTime);

        $event = $this->eventDispatcher->dispatch(
            new IndexRepositoryTimeSlotEvent($constraints, $query),
        );

        return $this->matchAndExecute($query, $event->getConstraints());
    }

    /**
     * Find all indices by the given Event model.
     */
    public function findByEvent(DomainObjectInterface $event): array|QueryResultInterface
    {
        return $this->findByEventTraversing(
            $event,
            true,
            true,
            0,
        );
    }

    /**
     * storage page selection.
     */
    protected function getStoragePageIds(): array
    {
        if (null !== $this->overridePageIds) {
            return $this->overridePageIds;
        }

        /** @var ConfigurationManager $configurationManager */
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $frameworkConfig = $configurationManager
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        return GeneralUtility::intExplode(',', (string)($frameworkConfig['persistence']['storagePid'] ?? ''));
    }

    /**
     * Get the default constraint for the queries.
     */
    protected function getDefaultConstraints(QueryInterface $query): array
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

        $event = $this->eventDispatcher->dispatch(
            new IndexRepositoryDefaultConstraintEvent([], $this->indexTypes, $this->additionalSlotArguments),
        );

        if ($event->getForeignIds()) {
            $constraints[] = $this->addForeignIdConstraints($query, $event->getForeignIds());
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
        ?\DateTimeInterface $endTime = null,
    ): void {
        /** @var AddTimeFrameConstraintsEvent $event */
        $event = $this->eventDispatcher->dispatch(new AddTimeFrameConstraintsEvent(
            $constraints,
            $query,
            $this->additionalSlotArguments,
            $startTime,
            $endTime,
        ));

        $this->addDateTimeFrameConstraint(
            $constraints,
            $query,
            $event->getStart(),
            $event->getEnd(),
            (bool)ConfigurationUtility::get('respectTimesInTimeFrameConstraints'),
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
        bool $respectTime = false,
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
                $dateConstraints[] = $query->logicalOr(
                    $query->greaterThan('endDate', $startDate),
                    $query->logicalAnd(
                        $query->equals('endDate', $startDate),
                        $query->logicalOr(
                            $query->equals('allDay', true),
                            $query->equals('openEndTime', true),
                            $query->greaterThanOrEqual('endTime', $startTime),
                        ),
                    ),
                );
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

                $dateConstraints[] = $query->logicalOr(
                    $query->lessThan('startDate', $endDate),
                    $query->logicalAnd(
                        $query->equals('startDate', $endDate),
                        $query->logicalOr(
                            $query->equals('allDay', true),
                            $query->lessThanOrEqual('startTime', $endTime),
                        ),
                    ),
                );
            }
        }

        /** @var ModifyDateTimeFrameConstraintEvent $event */
        $event = $this->eventDispatcher->dispatch(new ModifyDateTimeFrameConstraintEvent(
            $query,
            $start,
            $end,
            $respectTime,
            $dateConstraints,
        ));

        $constraints[] = $query->logicalAnd(...$event->getDateConstraints());
    }

    /**
     * Get the sorting.
     */
    protected function getSorting(string $direction, string $field = ''): array
    {
        if ('withrangelast' === $field) {
            return [
                'endDate' => $direction,
                'startDate' => $direction,
                'startTime' => $direction,
                'uid' => $direction,
            ];
        }
        if ('end' !== $field) {
            $field = 'start';
        }

        return [
            $field . 'Date' => $direction,
            $field . 'Time' => $direction,
            'uid' => $direction,
        ];
    }

    protected function addForeignIdConstraints(QueryInterface $query, array $foreignIds): ConstraintInterface
    {
        $foreignIdConstraints = [];
        foreach ($foreignIds as $table => $ids) {
            if (\is_int($table)) {
                // Plain integers (= deprecated old way, stays in for compatibility)
                // Old way, just accept foreignUids as provided, not checking the table.
                $foreignIdConstraints[] = $query->equals('foreignUid', $ids);
                @trigger_error(
                    'Using only foreign ID constraint without a table is deprecated and will be removed in a later version.',
                    \E_USER_DEPRECATED,
                );
            } elseif (\is_string($table) && \is_array($ids)) {
                // Table based values with array of foreign uids
                // Handle each table individually on the filters allowing for uids to be table specific.
                $foreignIdConstraints[] = $query->logicalAnd(
                    $query->equals('foreignTable', $table),
                    $query->in('foreignUid', $ids),
                );
            } elseif (\is_string($table) && \is_int($ids)) {
                // Table based single return value
                $foreignIdConstraints[] = $query->logicalAnd(
                    $query->equals('foreignTable', $table),
                    $query->in('foreignUid', [$ids]),
                );
            }
        }
        if (\count($foreignIdConstraints) > 1) {
            // Multiple valid tables should be grouped by "OR"
            // so it's either table_a with uids 1,3,4 OR table_b with uids 1,5,7
            $foreignIdConstraint = $query->logicalOr(...$foreignIdConstraints);
        } else {
            // Single constraint or no constraint should just be simply added
            $foreignIdConstraint = array_shift($foreignIdConstraints);
        }

        return $foreignIdConstraint;
    }
}
