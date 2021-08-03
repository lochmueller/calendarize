<?php

/**
 * Calendar.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use GeorgRinger\NumberedPagination\NumberedPagination;
use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Event\DetermineSearchEvent;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\EventUtility;
use HDNET\Calendarize\Utility\ExtensionConfigurationUtility;
use HDNET\Calendarize\Utility\TranslateUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;

/**
 * Calendar.
 */
class CalendarController extends AbstractController
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Init all actions.
     */
    public function initializeAction()
    {
        $this->addCacheTags(['calendarize']);

        parent::initializeAction();
        if (isset($this->settings['format'])) {
            $this->request->setFormat($this->settings['format']);
        }
        $this->indexRepository->setIndexTypes(GeneralUtility::trimExplode(',', $this->settings['configuration'], true));
        $additionalSlotArguments = [
            'contentRecord' => $this->configurationManager->getContentObject()->data,
            'settings' => $this->settings,
        ];
        $this->indexRepository->setAdditionalSlotArguments($additionalSlotArguments);

        if (isset($this->settings['sorting'])) {
            if (isset($this->settings['sortBy'])) {
                $this->indexRepository->setDefaultSortingDirection($this->settings['sorting'], $this->settings['sortBy']);
            } else {
                $this->indexRepository->setDefaultSortingDirection($this->settings['sorting']);
            }
        }

        if (isset($this->arguments['startDate'])) {
            $this->arguments['startDate']->getPropertyMappingConfiguration()
                ->setTypeConverterOption(
                    DateTimeConverter::class,
                    DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                    'Y-m-d'
                );
        }
        if (isset($this->arguments['endDate'])) {
            $this->arguments['endDate']->getPropertyMappingConfiguration()
                ->setTypeConverterOption(
                    DateTimeConverter::class,
                    DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                    'Y-m-d'
                );
        }
        if ($this->request->hasArgument('event') && 'detailAction' === $this->actionMethodName) {
            // default configuration
            $configurationName = $this->settings['configuration'];
            // configuration overwritten by argument?
            if ($this->request->hasArgument('extensionConfiguration')) {
                $configurationName = $this->request->getArgument('extensionConfiguration');
            }
            // get the configuration
            $configuration = ExtensionConfigurationUtility::get($configurationName);

            // get Event by Configuration and Uid
            $event = EventUtility::getOriginalRecordByConfiguration($configuration, (int)$this->request->getArgument('event'));
            $index = $this->indexRepository->findByEventTraversing($event, true, false, 1)->getFirst();

            // if there is a valid index in the event
            if ($index) {
                $this->redirect('detail', null, null, ['index' => $index]);
            }
        }
    }

    /**
     * Latest action.
     *
     * @param \HDNET\Calendarize\Domain\Model\Index $index
     * @param \DateTime                             $startDate
     * @param \DateTime                             $endDate
     * @param array                                 $customSearch *
     * @param int                                   $year
     * @param int                                   $month
     * @param int                                   $week
     *
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $startDate
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $endDate
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $customSearch
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function latestAction(
        Index $index = null,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = [],
        $year = null,
        $month = null,
        $week = null
    ) {
        $this->checkStaticTemplateIsIncluded();
        if (($index instanceof Index) && \in_array('detail', $this->getAllowedActions(), true)) {
            $this->forward('detail');
        }

        $this->addCacheTags(['calendarize_latest']);

        $search = $this->determineSearch($startDate, $endDate, $customSearch, $year, $month, null, $week);

        $this->eventExtendedAssignMultiple([
            'indices' => $search['indices'],
            'pagination' => $this->getPagination($search['indices']),
            'searchMode' => $search['searchMode'],
            'searchParameter' => [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'customSearch' => $customSearch,
                'year' => $year,
                'month' => $month,
                'week' => $week,
            ],
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Result action.
     *
     * @param \HDNET\Calendarize\Domain\Model\Index $index
     * @param \DateTime                             $startDate
     * @param \DateTime                             $endDate
     * @param array                                 $customSearch
     * @param int                                   $year
     * @param int                                   $month
     * @param int                                   $week
     *
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $startDate
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $endDate
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $customSearch
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function resultAction(
        Index $index = null,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = [],
        $year = null,
        $month = null,
        $week = null
    ) {
        $this->checkStaticTemplateIsIncluded();
        if (($index instanceof Index) && \in_array('detail', $this->getAllowedActions(), true)) {
            $this->forward('detail');
        }

        $this->addCacheTags(['calendarize_result']);

        $search = $this->determineSearch($startDate, $endDate, $customSearch, $year, $month, null, $week);

        $this->eventExtendedAssignMultiple([
            'indices' => $search['indices'],
            'pagination' => $this->getPagination($search['indices']),
            'searchMode' => $search['searchMode'],
            'searchParameter' => [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'customSearch' => $customSearch,
                'year' => $year,
                'month' => $month,
                'week' => $week,
            ],
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * List action.
     *
     * @param \HDNET\Calendarize\Domain\Model\Index $index
     * @param \DateTime                             $startDate
     * @param \DateTime                             $endDate
     * @param array                                 $customSearch *
     * @param int                                   $year
     * @param int                                   $month
     * @param int                                   $day
     * @param int                                   $week
     *
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $startDate
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $endDate
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $customSearch
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function listAction(
        Index $index = null,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = [],
        $year = null,
        $month = null,
        $day = null,
        $week = null
    ) {
        $this->checkStaticTemplateIsIncluded();
        if (($index instanceof Index) && \in_array('detail', $this->getAllowedActions(), true)) {
            $this->forward('detail');
        }

        $this->addCacheTags(['calendarize_list']);

        $search = $this->determineSearch($startDate, $endDate, $customSearch, $year, $month, $day, $week);

        $this->eventExtendedAssignMultiple([
            'indices' => $search['indices'],
            'pagination' => $this->getPagination($search['indices']),
            'searchMode' => $search['searchMode'],
            'searchParameter' => [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'customSearch' => $customSearch,
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'week' => $week,
            ],
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Shortcut.
     */
    public function shortcutAction()
    {
        $this->addCacheTags(['calendarize_shortcut']);
        list($table, $uid) = explode(':', $GLOBALS['TSFE']->currentRecord);
        $register = Register::getRegister();

        $event = null;
        foreach ($register as $key => $value) {
            if ($value['tableName'] === $table) {
                $repositoryName = ClassNamingUtility::translateModelNameToRepositoryName($value['modelName']);
                if (class_exists($repositoryName)) {
                    $repository = $this->objectManager->get($repositoryName);
                    $event = $repository->findByUid($uid);

                    $this->addCacheTags(['calendarize_' . lcfirst($value['uniqueRegisterKey']) . '_' . $event->getUid()]);
                    break;
                }
            }
        }

        if (!($event instanceof DomainObjectInterface)) {
            return 'Invalid object';
        }

        $limitEvents = (int)$this->settings['shortcutLimitEvents'];

        $fetchEvent = $this->indexRepository->findByEventTraversing($event, true, false, $limitEvents);
        if (\count($fetchEvent) <= 0) {
            $fetchEvent = $this->indexRepository->findByEventTraversing($event, false, true, $limitEvents, QueryInterface::ORDER_DESCENDING);
        }

        $this->view->assignMultiple([
            'pagination' => $this->getPagination($fetchEvent),
            'indices' => $fetchEvent,
        ]);
    }

    /**
     * Past action.
     *
     * @param int    $limit
     * @param string $sort
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function pastAction(
        $limit = 100,
        $sort = 'ASC'
    ) {
        $this->addCacheTags(['calendarize_past']);

        $limit = (int)($this->settings['limit']);
        $sort = $this->settings['sorting'];
        $this->checkStaticTemplateIsIncluded();
        $indices = $this->indexRepository->findByPast($limit, $sort);

        $this->eventExtendedAssignMultiple([
            'indices' => $indices,
            'pagination' => $this->getPagination($indices),
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Year action.
     *
     * @param int $year
     */
    public function yearAction($year = null)
    {
        $this->addCacheTags(['calendarize_year']);

        // use the thrid day, to avoid time shift problems in the timezone
        $date = DateTimeUtility::normalizeDateTime(3, 1, $year);
        $now = DateTimeUtility::getNow();
        if (null === $year || $now->format('Y') === $date->format('Y')) {
            $date = $now;
        }

        if ($this->isDateOutOfTypoScriptConfiguration($date)) {
            return $this->return404Page();
        }

        $this->eventExtendedAssignMultiple([
            'indices' => $this->indexRepository->findYear((int)$date->format('Y')),
            'date' => $date,
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Quarter action.
     *
     * @param int $year
     * @param int $quarter 1-4
     */
    public function quarterAction(int $year = null, int $quarter = null)
    {
        $this->addCacheTags(['calendarize_quarter']);

        $quarter = DateTimeUtility::normalizeQuarter($quarter);
        $date = DateTimeUtility::normalizeDateTime(1, 1 + (($quarter - 1) * 3), $year);

        if ($this->isDateOutOfTypoScriptConfiguration($date)) {
            return $this->return404Page();
        }

        $this->eventExtendedAssignMultiple([
            'indices' => $this->indexRepository->findQuarter((int)$date->format('Y'), $quarter),
            'date' => $date,
            'quarter' => $quarter,
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Month action.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     */
    public function monthAction($year = null, $month = null, $day = null)
    {
        $this->addCacheTags(['calendarize_month']);

        $date = DateTimeUtility::normalizeDateTime($day, $month, $year);
        $now = DateTimeUtility::getNow();
        $useCurrentDate = $now->format('Y-m') === $date->format('Y-m');
        if ($useCurrentDate) {
            $date = $now;
        }

        if ($this->isDateOutOfTypoScriptConfiguration($date)) {
            return $this->return404Page();
        }

        $this->eventExtendedAssignMultiple([
            'date' => $date,
            'selectDay' => $useCurrentDate,
            'ignoreSelectedDay' => !$useCurrentDate,
            'indices' => $this->indexRepository->findMonth((int)$date->format('Y'), (int)$date->format('n')),
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Week action.
     *
     * @param int|null $year
     * @param int|null $week
     */
    public function weekAction(?int $year = null, ?int $week = null)
    {
        $this->addCacheTags(['calendarize_week']);

        $now = DateTimeUtility::getNow();
        if (null === $year) {
            $year = (int)$now->format('o'); // 'o' instead of 'Y': http://php.net/manual/en/function.date.php#106974
        }
        if (null === $week) {
            $week = (int)$now->format('W');
        }
        $weekStart = (int)$this->settings['weekStart'];
        $firstDay = DateTimeUtility::convertWeekYear2DayMonthYear($week, $year, $weekStart);

        if ($this->isDateOutOfTypoScriptConfiguration($firstDay)) {
            return $this->return404Page();
        }

        $weekConfiguration = [
            '+0 day' => 2,
            '+1 days' => 2,
            '+2 days' => 2,
            '+3 days' => 2,
            '+4 days' => 2,
            '+5 days' => 1,
            '+6 days' => 1,
        ];

        $this->eventExtendedAssignMultiple([
            'firstDay' => $firstDay,
            'indices' => $this->indexRepository->findWeek($year, $week, $weekStart),
            'weekConfiguration' => $weekConfiguration,
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Day action.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     */
    public function dayAction($year = null, $month = null, $day = null)
    {
        $this->addCacheTags(['calendarize_day']);

        $date = DateTimeUtility::normalizeDateTime($day, $month, $year);
        $date->modify('+12 hours');

        if ($this->isDateOutOfTypoScriptConfiguration($date)) {
            return $this->return404Page();
        }

        $previous = clone $date;
        $previous->modify('-1 day');

        $next = clone $date;
        $next->modify('+1 day');

        $this->eventExtendedAssignMultiple([
            'indices' => $this->indexRepository->findDay((int)$date->format('Y'), (int)$date->format('n'), (int)$date->format('j')),
            'today' => $date,
            'previous' => $previous,
            'next' => $next,
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Detail action.
     *
     * @param \HDNET\Calendarize\Domain\Model\Index $index
     *
     * @return string
     */
    public function detailAction(Index $index = null)
    {
        if (null === $index) {
            // handle fallback for "strange language settings"
            if ($this->request->hasArgument('index')) {
                $indexId = (int)$this->request->getArgument('index');
                if ($indexId > 0) {
                    $index = $this->indexRepository->findByUid($indexId);
                }
            }

            if (null === $index) {
                if (!MathUtility::canBeInterpretedAsInteger($this->settings['listPid'])) {
                    return (string)TranslateUtility::get('noEventDetailView');
                }
                $this->eventExtendedRedirect(__CLASS__, __FUNCTION__ . 'noEvent');
            }
        }

        $this->addCacheTags(['calendarize_detail', 'calendarize_index_' . $index->getUid(), 'calendarize_' . lcfirst($index->getConfiguration()['uniqueRegisterKey']) . '_' . $index->getOriginalObject()->getUid()]);

        // Meta tags
        if ($index->getOriginalObject() instanceof Event) {
            /** @var Event $event */
            $event = $index->getOriginalObject();
            GeneralUtility::makeInstance(MetaTagManagerRegistry::class)->getManagerForProperty('og:title')->addProperty('og:title', $event->getTitle());
        }

        $this->eventExtendedAssignMultiple([
            'index' => $index,
            'domain' => GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'),
        ], __CLASS__, __FUNCTION__);

        return $this->view->render();
    }

    /**
     * Render the search view.
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array     $customSearch
     *
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $startDate
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $endDate
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $customSearch
     */
    public function searchAction(\DateTime $startDate = null, \DateTime $endDate = null, array $customSearch = [])
    {
        $this->addCacheTags(['calendarize_search']);

        $baseDate = DateTimeUtility::getNow();
        if (!($startDate instanceof \DateTimeInterface)) {
            $startDate = clone $baseDate;
        }
        if (!($endDate instanceof \DateTimeInterface)) {
            $endDate = clone $startDate;
            $modify = \is_string($this->settings['searchEndModifier']) ? $this->settings['searchEndModifier'] : '+30 days';
            $endDate->modify($modify);
        }
        $this->checkWrongDateOrder($startDate, $endDate);

        $this->eventExtendedAssignMultiple([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'customSearch' => $customSearch,
            'configurations' => $this->getCurrentConfigurations(),
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Render single items.
     */
    public function singleAction()
    {
        $this->addCacheTags(['calendarize_single']);

        $indicies = [];

        // prepare selection
        $selections = [];
        $configurations = $this->getCurrentConfigurations();
        foreach (GeneralUtility::trimExplode(',', $this->settings['singleItems']) as $item) {
            list($table, $uid) = BackendUtility::splitTable_Uid($item);
            foreach ($configurations as $configuration) {
                if ($configuration['tableName'] === $table) {
                    $selections[] = [
                        'configuration' => $configuration,
                        'uid' => $uid,
                    ];
                    break;
                }
            }
        }

        // fetch index
        foreach ($selections as $selection) {
            $this->indexRepository->setIndexTypes([$selection['configuration']['uniqueRegisterKey']]);
            $dummyIndex = new Index();
            $dummyIndex->setForeignTable($selection['configuration']['tableName']);
            $dummyIndex->setForeignUid($selection['uid']);

            $result = $this->indexRepository->findByTraversing($dummyIndex);
            $index = $result->getQuery()->setLimit(1)->execute()->getFirst();
            if (\is_object($index)) {
                $indicies[] = $index;
            } else {
                $result = $this->indexRepository->findByTraversing($dummyIndex, false, true);
                $index = $result->getQuery()->setLimit(1)->execute()->getFirst();
                if (\is_object($index)) {
                    $indicies[] = $index;
                }
            }
        }

        $this->eventExtendedAssignMultiple([
            'indicies' => $indicies,
            'configurations' => $configurations,
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Build the search structure.
     *
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @param array          $customSearch
     * @param int            $year
     * @param int            $month
     * @param int            $day
     * @param int            $week
     *
     * @return array
     */
    protected function determineSearch(
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = [],
        $year = null,
        $month = null,
        $day = null,
        $week = null
    ) {
        $searchMode = false;
        $this->checkWrongDateOrder($startDate, $endDate);
        if ($startDate || $endDate || !empty($customSearch)) {
            $searchMode = true;
            $limit = isset($this->settings['limit']) ? (int)$this->settings['limit'] : 0;
            $indices = $this->indexRepository->findBySearch($startDate, $endDate, $customSearch, $limit);
        } elseif (MathUtility::canBeInterpretedAsInteger($year) && MathUtility::canBeInterpretedAsInteger($month) && MathUtility::canBeInterpretedAsInteger($day)) {
            $indices = $this->indexRepository->findDay((int)$year, (int)$month, (int)$day);
        } elseif (MathUtility::canBeInterpretedAsInteger($year) && MathUtility::canBeInterpretedAsInteger($month)) {
            $indices = $this->indexRepository->findMonth((int)$year, (int)$month);
        } elseif (MathUtility::canBeInterpretedAsInteger($year) && MathUtility::canBeInterpretedAsInteger($week)) {
            $indices = $this->indexRepository->findWeek((int)$year, (int)$week, (int)$this->settings['weekStart']);
        } elseif (MathUtility::canBeInterpretedAsInteger($year)) {
            $indices = $this->indexRepository->findYear((int)$year);
        } else {
            // check if relative dates are enabled
            if ((bool)$this->settings['useRelativeDate']) {
                $overrideStartDateRelative = trim($this->settings['overrideStartRelative']);
                if ('' === $overrideStartDateRelative) {
                    $overrideStartDateRelative = 'now';
                }
                try {
                    $relativeDate = new \DateTime($overrideStartDateRelative);
                } catch (\Exception $e) {
                    $relativeDate = DateTimeUtility::getNow();
                }
                $overrideStartDate = $relativeDate->getTimestamp();
                $overrideEndDate = 0;
                $overrideEndDateRelative = trim($this->settings['overrideEndRelative']);
                if ('' !== $overrideStartDateRelative) {
                    try {
                        $relativeDate->modify($overrideEndDateRelative);
                        $overrideEndDate = $relativeDate->getTimestamp();
                    } catch (\Exception $e) {
                        // do nothing $overrideEndDate is 0
                    }
                }
            } else {
                $overrideStartDate = (int)$this->settings['overrideStartdate'];
                $overrideEndDate = (int)$this->settings['overrideEnddate'];
            }
            $indices = $this->indexRepository->findList(
                (int)$this->settings['limit'],
                $this->settings['listStartTime'],
                (int)$this->settings['listStartTimeOffsetHours'],
                $overrideStartDate,
                $overrideEndDate
            );
        }

        $event = new DetermineSearchEvent([
            'indices' => $indices,
            'searchMode' => $searchMode,
            'parameters' => [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'customSearch' => $customSearch,
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'week' => $week,
            ],
        ], $this->settings);

        $this->eventDispatcher->dispatch($event);

        return $event->getVariables();
    }

    protected function checkWrongDateOrder(\DateTime &$startDate = null, \DateTime &$endDate = null)
    {
        if ($startDate && $endDate && $endDate < $startDate) {
            // End date is before start date. So use start and end equals!
            $endDate = clone $startDate;
        }
    }

    /**
     * Creates the pagination logic for the results.
     *
     * @param QueryResultInterface $queryResult
     *
     * @return array
     */
    protected function getPagination(QueryResultInterface $queryResult): array
    {
        $paginateConfiguration = $this->settings['paginateConfiguration'] ?? [];
        $itemsPerPage = (int)($paginateConfiguration['itemsPerPage'] ?? 10);
        $maximumNumberOfLinks = (int)($paginateConfiguration['maximumNumberOfLinks'] ?? 10);

        $currentPage = $this->request->hasArgument('currentPage') ? (int)$this->request->getArgument('currentPage') : 1;

        $paginator = new QueryResultPaginator($queryResult, $currentPage, $itemsPerPage);
        if (class_exists(NumberedPagination::class)) {
            $pagination = new NumberedPagination($paginator, $maximumNumberOfLinks);
        } else {
            $pagination = new SimplePagination($paginator);
        }

        return [
            'paginator' => $paginator,
            'pagination' => $pagination,
        ];
    }

    /**
     * Get the allowed actions.
     *
     * @return array
     */
    protected function getAllowedActions(): array
    {
        $configuration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $allowedActions = [];
        foreach ($configuration['controllerConfiguration'] as $controllerName => $controllerActions) {
            $allowedActions[$controllerName] = $controllerActions['actions'];
        }

        return \is_array($allowedActions[__CLASS__]) ? $allowedActions[__CLASS__] : [];
    }

    /**
     * Get the current configurations.
     *
     * @return array
     */
    protected function getCurrentConfigurations()
    {
        $configurations = GeneralUtility::trimExplode(',', $this->settings['configuration'], true);
        $return = [];
        foreach (Register::getRegister() as $key => $configuration) {
            if (\in_array($key, $configurations, true)) {
                $return[] = $configuration;
            }
        }

        return $return;
    }
}
