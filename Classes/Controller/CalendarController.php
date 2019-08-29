<?php

/**
 * Calendar.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\EventUtility;
use HDNET\Calendarize\Utility\ExtensionConfigurationUtility;
use HDNET\Calendarize\Utility\TranslateUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Calendar.
 */
class CalendarController extends AbstractController
{
    /**
     * Init all actions.
     */
    public function initializeAction()
    {
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
                    $this->settings['dateFormat']
                );
        }
        if (isset($this->arguments['endDate'])) {
            $this->arguments['endDate']->getPropertyMappingConfiguration()
                ->setTypeConverterOption(
                    DateTimeConverter::class,
                    DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                    $this->settings['dateFormat']
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
            $event = EventUtility::getOriginalRecordByConfiguration($configuration, (int) $this->request->getArgument('event'));
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
     * @ignorevalidation $startDate
     * @ignorevalidation $endDate
     * @ignorevalidation $customSearch
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

        $search = $this->determineSearch($startDate, $endDate, $customSearch, $year, $month, null, $week);

        $this->slotExtendedAssignMultiple([
            'indices' => $search['indices'],
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
     * @ignorevalidation $startDate
     * @ignorevalidation $endDate
     * @ignorevalidation $customSearch
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

        $search = $this->determineSearch($startDate, $endDate, $customSearch, $year, $month, null, $week);

        $this->slotExtendedAssignMultiple([
            'indices' => $search['indices'],
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
     * @ignorevalidation $startDate
     * @ignorevalidation $endDate
     * @ignorevalidation $customSearch
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

        $search = $this->determineSearch($startDate, $endDate, $customSearch, $year, $month, $day, $week);

        $this->slotExtendedAssignMultiple([
            'indices' => $search['indices'],
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
        list($table, $uid) = \explode(':', $GLOBALS['TSFE']->currentRecord);
        $register = Register::getRegister();

        $event = null;
        foreach ($register as $key => $value) {
            if ($value['tableName'] === $table) {
                $repositoryName = ClassNamingUtility::translateModelNameToRepositoryName($value['modelName']);
                if (\class_exists($repositoryName)) {
                    $objectManager = new ObjectManager();
                    $repository = $objectManager->get($repositoryName);
                    $event = $repository->findByUid($uid);
                }
            }
        }

        if (!($event instanceof DomainObjectInterface)) {
            return 'Invalid object';
        }

        $fetchEvent = $this->indexRepository->findByEventTraversing($event, true, false, 1);
        if (\count($fetchEvent) <= 0) {
            $fetchEvent = $this->indexRepository->findByEventTraversing($event, false, true, 1, QueryInterface::ORDER_DESCENDING);
        }

        $this->view->assignMultiple([
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
        $limit = (int) ($this->settings['limit']);
        $sort = $this->settings['sorting'];
        $this->checkStaticTemplateIsIncluded();
        $this->slotExtendedAssignMultiple([
           'indices' => $this->indexRepository->findByPast($limit, $sort),
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Year action.
     *
     * @param int $year
     */
    public function yearAction($year = null)
    {
        $date = DateTimeUtility::normalizeDateTime(1, 1, $year);

        $this->slotExtendedAssignMultiple([
            'indices' => $this->indexRepository->findYear((int) $date->format('Y')),
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
        $quarter = DateTimeUtility::normalizeQuarter($quarter);
        $date = DateTimeUtility::normalizeDateTime(1, 1 + (($quarter - 1) * 3), $year);

        $this->slotExtendedAssignMultiple([
            'indices' => $this->indexRepository->findQuarter((int) $date->format('Y'), $quarter),
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
        $date = DateTimeUtility::normalizeDateTime($day, $month, $year);

        $this->slotExtendedAssignMultiple([
            'date' => $date,
            'indices' => $this->indexRepository->findMonth((int) $date->format('Y'), (int) $date->format('n')),
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Week action.
     *
     * @param int $year
     * @param int $week
     */
    public function weekAction($year = null, $week = null)
    {
        $now = DateTimeUtility::getNow();
        if (null === $year) {
            $year = $now->format('o'); // 'o' instead of 'Y': http://php.net/manual/en/function.date.php#106974
        }
        if (null === $week) {
            $week = $now->format('W');
        }
        $weekStart = (int) $this->settings['weekStart'];
        $firstDay = DateTimeUtility::convertWeekYear2DayMonthYear((int) $week, $year, $weekStart);
        $timezone = DateTimeUtility::getTimeZone();
        $firstDay->setTimezone($timezone);
        $firstDay->setTime(0, 0, 0);

        $weekConfiguration = [
            '+0 day' => 2,
            '+1 days' => 2,
            '+2 days' => 2,
            '+3 days' => 2,
            '+4 days' => 2,
            '+5 days' => 1,
            '+6 days' => 1,
        ];

        $this->slotExtendedAssignMultiple([
            'firstDay' => $firstDay,
            'indices' => $this->indexRepository->findWeek($year, $week, $this->settings['weekStart']),
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
        $date = DateTimeUtility::normalizeDateTime($day, $month, $year);
        $date->modify('+12 hours');

        $previous = clone $date;
        $previous->modify('-1 day');

        $next = clone $date;
        $next->modify('+1 day');

        $this->slotExtendedAssignMultiple([
            'indices' => $this->indexRepository->findDay((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j')),
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
                $indexId = (int) $this->request->getArgument('index');
                if ($indexId > 0) {
                    $index = $this->indexRepository->findByUid($indexId);
                }
            }

            if (null === $index) {
                if (!MathUtility::canBeInterpretedAsInteger($this->settings['listPid'])) {
                    return (string) TranslateUtility::get('noEventDetailView');
                }
                $this->slottedRedirect(__CLASS__, __FUNCTION__ . 'noEvent');
            }
        }

        $this->slotExtendedAssignMultiple([
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
     * @ignorevalidation $startDate
     * @ignorevalidation $endDate
     * @ignorevalidation $customSearch
     */
    public function searchAction(\DateTime $startDate = null, \DateTime $endDate = null, array $customSearch = [])
    {
        $baseDate = DateTimeUtility::getNow();
        if (!($startDate instanceof \DateTimeInterface)) {
            $startDate = clone $baseDate;
        }
        if (!($endDate instanceof \DateTimeInterface)) {
            $endDate = clone $startDate;
            $modify = \is_string($this->settings['searchEndModifier']) ? $this->settings['searchEndModifier'] : '+30 days';
            $endDate->modify($modify);
        }

        $this->slotExtendedAssignMultiple([
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
            }
        }

        $this->slotExtendedAssignMultiple([
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
        if ($startDate || $endDate || !empty($customSearch)) {
            $searchMode = true;
            $limit = isset($this->settings['limit']) ? (int) $this->settings['limit'] : 0;
            $indices = $this->indexRepository->findBySearch($startDate, $endDate, $customSearch, $limit);
        } elseif (MathUtility::canBeInterpretedAsInteger($year) && MathUtility::canBeInterpretedAsInteger($month) && MathUtility::canBeInterpretedAsInteger($day)) {
            $indices = $this->indexRepository->findDay((int) $year, (int) $month, (int) $day);
        } elseif (MathUtility::canBeInterpretedAsInteger($year) && MathUtility::canBeInterpretedAsInteger($month)) {
            $indices = $this->indexRepository->findMonth((int) $year, (int) $month);
        } elseif (MathUtility::canBeInterpretedAsInteger($year) && MathUtility::canBeInterpretedAsInteger($week)) {
            $indices = $this->indexRepository->findWeek($year, $week, $this->settings['weekStart']);
        } elseif (MathUtility::canBeInterpretedAsInteger($year)) {
            $indices = $this->indexRepository->findYear((int) $year);
        } else {
            // check if relative dates are enabled
            if ((bool) $this->settings['useRelativeDate']) {
                $overrideStartDateRelative = \trim($this->settings['overrideStartRelative']);
                if ('' === $overrideStartDateRelative) {
                    $overrideStartDateRelative = 'now';
                }
                try {
                    $relativeDate = new \DateTime($overrideStartDateRelative);
                } catch (\Exception $e) {
                    $relativeDate = new \DateTime();
                }
                $overrideStartDate = $relativeDate->getTimestamp();
                $overrideEndDate = 0;
                $overrideEndDateRelative = \trim($this->settings['overrideEndRelative']);
                if ('' !== $overrideStartDateRelative) {
                    try {
                        $relativeDate->modify($overrideEndDateRelative);
                        $overrideEndDate = $relativeDate->getTimestamp();
                    } catch (\Exception $e) {
                        // do nothing $overrideEndDate is 0
                    }
                }
            } else {
                $overrideStartDate = (int) $this->settings['overrideStartdate'];
                $overrideEndDate = (int) $this->settings['overrideEnddate'];
            }
            $indices = $this->indexRepository->findList(
                (int) $this->settings['limit'],
                $this->settings['listStartTime'],
                (int) $this->settings['listStartTimeOffsetHours'],
                $overrideStartDate,
                $overrideEndDate
            );
        }

        // use this variable in your extension to add more custom variables
        $variables = [
            'extended' => [
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
            ],
        ];
        $variables['settings'] = $this->settings;

        $dispatcher = $this->objectManager->get(Dispatcher::class);
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        return $variables['extended'];
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

        return \is_array($allowedActions['Calendar']) ? $allowedActions['Calendar'] : [];
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

    /**
     * A redirect that have a slot included.
     *
     * @param string $signalClassName name of the signal class: __CLASS__
     * @param string $signalName      name of the signal: __FUNCTION__
     * @param array  $variables       optional: if not set use the defaults
     */
    protected function slottedRedirect($signalClassName, $signalName, $variables = null)
    {
        // set default variables for the redirect
        if (null === $variables) {
            $variables['extended'] = [
                'actionName' => 'list',
                'controllerName' => null,
                'extensionName' => null,
                'arguments' => [],
                'pageUid' => $this->settings['listPid'],
                'delay' => 0,
                'statusCode' => 301,
            ];
            $variables['extended']['pluginHmac'] = $this->calculatePluginHmac();
            $variables['settings'] = $this->settings;
        }

        $dispatcher = $this->objectManager->get(Dispatcher::class);
        $variables = $dispatcher->dispatch($signalClassName, $signalName, $variables);

        $this->redirect(
            $variables['extended']['actionName'],
            $variables['extended']['controllerName'],
            $variables['extended']['extensionName'],
            $variables['extended']['arguments'],
            $variables['extended']['pageUid'],
            $variables['extended']['delay'],
            $variables['extended']['statusCode']
        );
    }
}
