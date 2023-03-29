<?php

/**
 * Calendar.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Event\DetermineSearchEvent;
use HDNET\Calendarize\Event\PaginationEvent;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\EventUtility;
use HDNET\Calendarize\Utility\ExtensionConfigurationUtility;
use HDNET\Calendarize\Utility\TranslateUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 * Calendar.
 */
class CalendarController extends AbstractCompatibilityController
{
    /**
     * Init all actions.
     */
    public function initializeAction()
    {
        $this->addCacheTags(['calendarize']);

        parent::initializeAction();
        if (isset($this->settings['format'])) {
            $this->request = $this->request->withFormat($this->settings['format']);
        }
        $this->indexRepository->setIndexTypes(GeneralUtility::trimExplode(',', $this->settings['configuration'] ?? '', true));
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
            $configurationName = $this->settings['configuration'] ?? '';
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
     */
    #[Extbase\IgnoreValidation(['argumentName' => 'startDate'])]
    #[Extbase\IgnoreValidation(['argumentName' => 'endDate'])]
    #[Extbase\IgnoreValidation(['argumentName' => 'customSearch'])]
    public function latestAction(
        Index $index = null,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = [],
        int $year = 0,
        int $month = 0,
        int $week = 0
    ): ResponseInterface {
        $this->checkStaticTemplateIsIncluded();
        if (($index instanceof Index) && in_array('detail', $this->getAllowedActions(), true)) {
            return new ForwardResponse('detail');
        }

        $this->addCacheTags(['calendarize_latest']);

        $search = $this->determineSearch($startDate, $endDate, $customSearch, $year, $month, 0, $week);

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
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Result action.
     */
    #[Extbase\IgnoreValidation(['argumentName' => 'startDate'])]
    #[Extbase\IgnoreValidation(['argumentName' => 'endDate'])]
    #[Extbase\IgnoreValidation(['argumentName' => 'customSearch'])]
    public function resultAction(
        Index $index = null,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = [],
        int $year = 0,
        int $month = 0,
        int $week = 0
    ): ResponseInterface {
        $this->checkStaticTemplateIsIncluded();
        if (($index instanceof Index) && in_array('detail', $this->getAllowedActions(), true)) {
            return new ForwardResponse('detail');
        }

        $this->addCacheTags(['calendarize_result']);

        $search = $this->determineSearch($startDate, $endDate, $customSearch, $year, $month, 0, $week);

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
        return $this->htmlResponse($this->view->render());
    }

    /**
     * List action.
     */
    #[Extbase\IgnoreValidation(['argumentName' => 'startDate'])]
    #[Extbase\IgnoreValidation(['argumentName' => 'endDate'])]
    #[Extbase\IgnoreValidation(['argumentName' => 'customSearch'])]
    public function listAction(
        Index $index = null,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = [],
        int $year = 0,
        int $month = 0,
        int $day = 0,
        int $week = 0
    ): ResponseInterface {
        $this->checkStaticTemplateIsIncluded();
        if (($index instanceof Index) && in_array('detail', $this->getAllowedActions(), true)) {
            return new ForwardResponse('detail');
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
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Shortcut.
     */
    public function shortcutAction(): ResponseInterface
    {
        $this->addCacheTags(['calendarize_shortcut']);
        list($table, $uid) = explode(':', $GLOBALS['TSFE']->currentRecord);
        $register = Register::getRegister();

        $event = null;
        foreach ($register as $value) {
            if ($value['tableName'] === $table) {
                $repositoryName = ClassNamingUtility::translateModelNameToRepositoryName($value['modelName']);
                if (class_exists($repositoryName)) {
                    $repository = GeneralUtility::makeInstance($repositoryName);
                    $event = $repository->findByUid($uid);

                    $this->addCacheTags(['calendarize_' . lcfirst($value['uniqueRegisterKey']) . '_' . $event->getUid()]);
                    break;
                }
            }
        }

        if (!($event instanceof DomainObjectInterface)) {
            return $this->htmlResponse('Invalid object');
        }

        $limitEvents = (int)($this->settings['shortcutLimitEvents'] ?? 1);

        $fetchEvent = $this->indexRepository->findByEventTraversing($event, true, false, $limitEvents);
        if (count($fetchEvent) <= 0) {
            $fetchEvent = $this->indexRepository->findByEventTraversing($event, false, true, $limitEvents, QueryInterface::ORDER_DESCENDING);
        }

        $this->view->assignMultiple([
            'pagination' => $this->getPagination($fetchEvent),
            'indices' => $fetchEvent,
        ]);
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Past action.
     */
    public function pastAction(
        int $limit = 100,
        string $sort = 'ASC'
    ): ResponseInterface {
        $this->addCacheTags(['calendarize_past']);

        $limit = $limit ?: (int)($this->settings['limit']);
        $sort = $sort ?: $this->settings['sorting'];
        $this->checkStaticTemplateIsIncluded();
        $listStartTime = (string)$this->settings['listStartTime'];
        $indices = $this->indexRepository->findByPast($limit, $sort, $listStartTime);

        $this->eventExtendedAssignMultiple([
            'indices' => $indices,
            'pagination' => $this->getPagination($indices),
        ], __CLASS__, __FUNCTION__);
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Year action.
     */
    public function yearAction(int $year = 0): ResponseInterface
    {
        $this->addCacheTags(['calendarize_year']);

        // use the third day, to avoid time shift problems in the timezone
        $date = DateTimeUtility::normalizeDateTime(3, 1, $year);
        $now = DateTimeUtility::getNow();
        if (0 === $year || $now->format('Y') === $date->format('Y')) {
            $date = $now;
        }

        if ($this->isDateOutOfTypoScriptConfiguration($date)) {
            return $this->return404Page();
        }

        $indices = $this->indexRepository->findYear((int)$date->format('Y'));

        $this->eventExtendedAssignMultiple([
            'indices' => $indices,
            'pagination' => $this->getPagination($indices),
            'date' => $date,
        ], __CLASS__, __FUNCTION__);
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Quarter action.
     *
     * @param int $year
     * @param int $quarter 1-4
     *
     * @return ResponseInterface
     */
    public function quarterAction(int $year = 0, int $quarter = 1): ResponseInterface
    {
        $this->addCacheTags(['calendarize_quarter']);

        $quarter = DateTimeUtility::normalizeQuarter($quarter);
        $date = DateTimeUtility::normalizeDateTime(1, 1 + (($quarter - 1) * 3), $year);

        if ($this->isDateOutOfTypoScriptConfiguration($date)) {
            return $this->return404Page();
        }

        $indices = $this->indexRepository->findQuarter((int)$date->format('Y'), $quarter);

        $this->eventExtendedAssignMultiple([
            'indices' => $indices,
            'pagination' => $this->getPagination($indices),
            'date' => $date,
            'quarter' => $quarter,
        ], __CLASS__, __FUNCTION__);
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Month action.
     */
    public function monthAction(int $year = 0, int $month = 0, int $day = 0): ResponseInterface
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

        $indices = $this->indexRepository->findMonth((int)$date->format('Y'), (int)$date->format('n'));

        $this->eventExtendedAssignMultiple([
            'date' => $date,
            'selectDay' => $useCurrentDate,
            'ignoreSelectedDay' => !$useCurrentDate,
            'indices' => $indices,
            'pagination' => $this->getPagination($indices),
        ], __CLASS__, __FUNCTION__);
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Week action.
     */
    public function weekAction(int $year = null, int $week = null): ResponseInterface
    {
        $this->addCacheTags(['calendarize_week']);

        $now = DateTimeUtility::getNow();
        if (null === $year) {
            $year = (int)$now->format('o'); // 'o' instead of 'Y': http://php.net/manual/en/function.date.php#106974
        }
        if (null === $week) {
            $week = (int)$now->format('W');
        }
        $weekStart = (int)($this->settings['weekStart'] ?? 1);
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

        $indices = $this->indexRepository->findWeek($year, $week, $weekStart);

        $this->eventExtendedAssignMultiple([
            'firstDay' => $firstDay,
            'indices' => $indices,
            'pagination' => $this->getPagination($indices),
            'weekConfiguration' => $weekConfiguration,
        ], __CLASS__, __FUNCTION__);
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Day action.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     *
     * @return ResponseInterface
     */
    public function dayAction(int $year = 0, int $month = 0, int $day = 0): ResponseInterface
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

        $indices = $this->indexRepository->findDay(
            (int)$date->format('Y'),
            (int)$date->format('n'),
            (int)$date->format('j')
        );

        $this->eventExtendedAssignMultiple([
            'indices' => $indices,
            'pagination' => $this->getPagination($indices),
            'today' => $date,
            'previous' => $previous,
            'next' => $next,
        ], __CLASS__, __FUNCTION__);
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Detail action.
     */
    public function detailAction(Index $index = null): ResponseInterface
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
                    return $this->htmlResponse(TranslateUtility::get('noEventDetailView'));
                }
                $this->eventExtendedRedirect(__CLASS__, __FUNCTION__ . 'noEvent');
            }
        }
        $uniqueRegisterKey = $index->getConfiguration()['uniqueRegisterKey'];
        $originalObject = $index->getOriginalObject();
        if (!$originalObject) {
            $this->eventExtendedRedirect(__CLASS__, __FUNCTION__ . 'noEvent');
        }

        $this->addCacheTags(
            ['calendarize_detail', 'calendarize_index_' . $index->getUid(), 'calendarize_'
                . lcfirst($uniqueRegisterKey)
                . '_'
                . $originalObject->getUid(),]
        );

        // Meta tags
        if ($index->getOriginalObject() instanceof Event) {
            /** @var Event $event */
            $event = $index->getOriginalObject();
            $metaTagManagerRegistry = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
            $metaTagManagerRegistry->getManagerForProperty('og:title')->addProperty('og:title', $event->getTitle());
            $metaTagManagerRegistry->getManagerForProperty('og:description')->addProperty('og:description', $event->getAbstract());

            $images = $event->getImages();
            if (isset($images[0])) {
                $imageService = GeneralUtility::makeInstance(ImageService::class);
                $processingInstructions = ['minWidth' => 600, 'minHeight' => 315, 'maxWidth' => 1200, 'maxHeight' => 630];
                $processedImage = $imageService->applyProcessingInstructions($images[0]->getOriginalResource(), $processingInstructions);
                $imageUrl = $this->getBaseUri() . $imageService->getImageUri($processedImage);
                $metaTagManagerRegistry->getManagerForProperty('og:image')->addProperty('og:image', $imageUrl);
            }
        }

        $this->eventExtendedAssignMultiple([
            'index' => $index,
            'domain' => GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'),
        ], __CLASS__, __FUNCTION__);
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Render the search view.
     *
     * @param ?\DateTime $startDate
     * @param ?\DateTime $endDate
     * @param array $customSearch
     *
     * @return ResponseInterface
     */
    #[Extbase\IgnoreValidation(['argumentName' => 'startDate'])]
    #[Extbase\IgnoreValidation(['argumentName' => 'endDate'])]
    #[Extbase\IgnoreValidation(['argumentName' => 'customSearch'])]
    public function searchAction(
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = []
    ): ResponseInterface {
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
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Render single items.
     */
    public function singleAction(): ResponseInterface
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
            $dummyIndex->setForeignUid((int)$selection['uid']);

            $result = $this->indexRepository->findByTraversing($dummyIndex);
            $index = $result->getQuery()->setLimit(1)->execute()->getFirst();
            if (is_object($index)) {
                $indicies[] = $index;
            } else {
                $result = $this->indexRepository->findByTraversing($dummyIndex, false, true);
                $index = $result->getQuery()->setLimit(1)->execute()->getFirst();
                if (is_object($index)) {
                    $indicies[] = $index;
                }
            }
        }

        $this->eventExtendedAssignMultiple([
            'indicies' => $indicies,
            'configurations' => $configurations,
        ], __CLASS__, __FUNCTION__);
        return $this->htmlResponse($this->view->render());
    }

    /**
     * Build the search structure.
     */
    protected function determineSearch(
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = [],
        int $year = 0,
        int $month = 0,
        int $day = 0,
        int $week = 0
    ): array {
        $searchMode = false;
        [$startDate, $endDate] = $this->checkWrongDateOrder($startDate, $endDate);
        if ($startDate || $endDate || !empty($customSearch)) {
            $searchMode = true;
            $limit = (int)($this->settings['limit'] ?? 0);
            $indices = $this->indexRepository->findBySearch($startDate, $endDate, $customSearch, $limit);
        } elseif ($year > 0 && $month > 0 && $day > 0) {
            $indices = $this->indexRepository->findDay($year, $month, $day);
        } elseif ($year > 0 && $month > 0) {
            $indices = $this->indexRepository->findMonth($year, $month);
        } elseif ($year > 0 && $week > 0) {
            $indices = $this->indexRepository->findWeek($year, $week, (int)($this->settings['weekStart'] ?? 1));
        } elseif ($year > 0) {
            $indices = $this->indexRepository->findYear($year);
        } else {
            // check if relative dates are enabled
            if ($this->settings['useRelativeDate'] ?? false) {
                $overrideStartDateRelative = trim($this->settings['overrideStartRelative'] ?? '');
                if ('' === $overrideStartDateRelative) {
                    $overrideStartDateRelative = 'now';
                }
                try {
                    $relativeDate = new \DateTime($overrideStartDateRelative);
                } catch (\Exception $exception) {
                    $relativeDate = DateTimeUtility::getNow();
                }
                $overrideStartDate = $relativeDate->getTimestamp();
                $overrideEndDate = 0;
                $overrideEndDateRelative = trim($this->settings['overrideEndRelative'] ?? '');
                if ('' !== $overrideEndDateRelative) {
                    try {
                        $relativeDate->modify($overrideEndDateRelative);
                        $overrideEndDate = $relativeDate->getTimestamp();
                    } catch (\Exception $exception) {
                        // do nothing $overrideEndDate is 0
                    }
                }
            } else {
                $overrideStartDate = (int)($this->settings['overrideStartdate'] ?? 0);
                $overrideEndDate = (int)($this->settings['overrideEnddate'] ?? 0);
            }
            $indices = $this->indexRepository->findList(
                (int)($this->settings['limit'] ?? 0),
                ($this->settings['listStartTime'] ?? 0),
                (int)($this->settings['listStartTimeOffsetHours'] ?? 0),
                $overrideStartDate,
                $overrideEndDate,
                (bool)($this->settings['ignoreStoragePid'] ?? false)
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

    protected function checkWrongDateOrder(\DateTime $startDate = null, \DateTime &$endDate = null): array
    {
        if ($startDate && $endDate && $endDate < $startDate) {
            // End date is before start date. So use start and end equals!
            $tmp = $startDate;
            $startDate = $endDate;
            $endDate = $tmp;
            unset($tmp);
        }
        return [$startDate, $endDate];
    }

    /**
     * Creates the pagination logic for the results.
     */
    protected function getPagination(QueryResultInterface $queryResult): array
    {
        $paginateConfiguration = $this->settings['paginateConfiguration'] ?? [];
        $itemsPerPage = (int)($paginateConfiguration['itemsPerPage'] ?: 10);
        $maximumNumberOfLinks = (int)($paginateConfiguration['maximumNumberOfLinks'] ?? 10);
        $currentPage = $this->request->hasArgument('currentPage') ?
            (int)$this->request->getArgument('currentPage')
            : 1;

        $paginator = new QueryResultPaginator($queryResult, $currentPage, $itemsPerPage);
        $pagination = new SimplePagination($paginator);

        $event = new PaginationEvent(
            $paginator,
            $pagination,
            $paginateConfiguration,
            [
                'itemsPerPage' => $itemsPerPage,
                'maximumNumberOfLinks' => $maximumNumberOfLinks,
                'currentPage' => $currentPage,
            ]
        );
        $this->eventDispatcher->dispatch($event);
        $paginator = $event->getPaginator();
        $pagination = $event->getPagination();

        return [
            'paginator' => $paginator,
            'pagination' => $pagination,
        ];
    }

    /**
     * Get the allowed actions.
     */
    protected function getAllowedActions(): array
    {
        $configuration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
        $allowedActions = [];
        foreach ($configuration['controllerConfiguration'] as $controllerName => $controllerActions) {
            $allowedActions[$controllerName] = $controllerActions['actions'];
        }

        return is_array($allowedActions[__CLASS__]) ? $allowedActions[__CLASS__] : [];
    }

    /**
     * Get the current configurations.
     */
    protected function getCurrentConfigurations(): array
    {
        $configurations = GeneralUtility::trimExplode(',', $this->settings['configuration'] ?? '', true);
        $return = [];
        foreach (Register::getRegister() as $key => $configuration) {
            if (in_array($key, $configurations, true)) {
                $return[] = $configuration;
            }
        }

        return $return;
    }

    protected function getBaseUri(): string
    {
        $request = $GLOBALS['TYPO3_REQUEST'];
        return $request->getAttribute('normalizedParams')->getSiteUrl();
    }
}
