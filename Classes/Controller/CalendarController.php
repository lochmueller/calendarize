<?php
/**
 * Calendar
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Calendar
 */
class CalendarController extends AbstractController
{

    /**
     * The index repository
     *
     * @var \HDNET\Calendarize\Domain\Repository\IndexRepository
     * @inject
     */
    protected $indexRepository;

    /**
     * Init all actions
     */
    public function initializeAction()
    {
        parent::initializeAction();
        $this->indexRepository->setIndexTypes(GeneralUtility::trimExplode(',', $this->settings['configuration']));
        $this->indexRepository->setContentRecord($this->configurationManager->getContentObject()->data);
        if (isset($this->settings['sorting'])) {
            $this->indexRepository->setDefaultSortingDirection($this->settings['sorting']);
        }

        if (isset($this->arguments['startDate'])) {
            $this->arguments['startDate']->getPropertyMappingConfiguration()
                ->setTypeConverterOption('TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\DateTimeConverter',
                    DateTimeConverter::CONFIGURATION_DATE_FORMAT, $this->settings['dateFormat']);
        }
        if (isset($this->arguments['endDate'])) {
            $this->arguments['endDate']->getPropertyMappingConfiguration()
                ->setTypeConverterOption('TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\DateTimeConverter',
                    DateTimeConverter::CONFIGURATION_DATE_FORMAT, $this->settings['dateFormat']);
        }
    }

    /**
     * Latest action
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
        $this->listAction($index, $startDate, $endDate, $customSearch, $year, $month, $week);
    }

    /**
     * Result action
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
    public function resultAction(
        Index $index = null,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = [],
        $year = null,
        $month = null,
        $week = null
    ) {
        $this->listAction($index, $startDate, $endDate, $customSearch, $year, $month, $week);
    }

    /**
     * List action
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
    public function listAction(
        Index $index = null,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = [],
        $year = null,
        $month = null,
        $week = null
    ) {
        if (($index instanceof Index) && in_array('detail', $this->getAllowedActions())) {
            $this->forward('detail');
        }

        $searchMode = false;
        if ($startDate || $endDate || $customSearch) {
            $searchMode = true;
            $indices = $this->indexRepository->findBySearch($startDate, $endDate, $customSearch);
        } elseif (MathUtility::canBeInterpretedAsInteger($year) && MathUtility::canBeInterpretedAsInteger($month)) {
            $indices = $this->indexRepository->findMonth($year, $month);
        } elseif (MathUtility::canBeInterpretedAsInteger($year) && MathUtility::canBeInterpretedAsInteger($week)) {
            $indices = $this->indexRepository->findWeek($year, $week);
        } elseif (MathUtility::canBeInterpretedAsInteger($year)) {
            $indices = $this->indexRepository->findYear($year);
        } else {
            $indices = $this->indexRepository->findList((int)$this->settings['limit']);
        }

        $this->slotExtendedAssignMultiple([
            'indices'    => $indices,
            'searchMode' => $searchMode
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Year action
     *
     * @param int $year
     *
     * @return void
     */
    public function yearAction($year = null)
    {
        $date = DateTimeUtility::normalizeDateTime(1, 1, $year);

        $this->slotExtendedAssignMultiple([
            'indices' => $this->indexRepository->findYear($date->format('Y')),
            'date'    => $date
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Month action
     *
     * @param int $year
     * @param int $month
     *
     * @return void
     */
    public function monthAction($year = null, $month = null)
    {
        $date = DateTimeUtility::normalizeDateTime(1, $month, $year);

        $this->slotExtendedAssignMultiple([
            'date'    => $date,
            'indices' => $this->indexRepository->findMonth($date->format('Y'), $date->format('n')),
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Week action
     *
     * @param int $year
     * @param int $week
     *
     * @return void
     */
    public function weekAction($year = null, $week = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        if ($week === null) {
            $week = date('W');
        }
        $firstDay = DateTimeUtility::convertWeekYear2DayMonthYear($week, $year);
        $firstDay->setTime(0, 0, 0);
        $this->slotExtendedAssignMultiple([
            'firstDay' => $firstDay,
            'indices'  => $this->indexRepository->findWeek($year, $week),
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Day action
     *
     * @param int $year
     * @param int $month
     * @param int $day
     *
     * @return void
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
            'indices'  => $this->indexRepository->findDay($date->format('Y'), $date->format('n'), $date->format('j')),
            'today'    => $date,
            'previous' => $previous,
            'next'     => $next,
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Detail action
     *
     * @param \HDNET\Calendarize\Domain\Model\Index $index
     *
     * @return string
     */
    public function detailAction(Index $index = null)
    {
        if ($index === null) {
            if (!MathUtility::canBeInterpretedAsInteger($this->settings['listPid'])) {
                return LocalizationUtility::translate('noEventDetailView', 'calendarize');
            }
            $this->redirect('list', null, null, [], null, $this->settings['listPid'], 301);
        }

        $this->slotExtendedAssignMultiple([
            'index'  => $index,
            'domain' => GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')
        ], __CLASS__, __FUNCTION__);

        return $this->view->render();
    }

    /**
     * Render the search view
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
        if (!($startDate instanceof \DateTime)) {
            $startDate = new \DateTime('now', DateTimeUtility::getTimeZone());
        }
        if (!($endDate instanceof \DateTime)) {
            $endDate = new \DateTime('+1 month', DateTimeUtility::getTimeZone());
        }

        $this->slotExtendedAssignMultiple([
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'customSearch'   => $customSearch,
            'configurations' => $this->getCurrentConfigurations()
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Get the allowed actions
     *
     * @return array
     */
    protected function getAllowedActions()
    {
        $configuration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $allowedControllerActions = [];
        foreach ($configuration['controllerConfiguration'] as $controllerName => $controllerActions) {
            $allowedControllerActions[$controllerName] = $controllerActions['actions'];
        }
        return isset($allowedControllerActions['Calendar']) ? $allowedControllerActions['Calendar'] : [];
    }

    /**
     * Get the current configurations
     *
     * @return array
     */
    protected function getCurrentConfigurations()
    {
        $configurations = GeneralUtility::trimExplode(',', $this->settings['configuration'], true);
        $return = [];
        foreach (Register::getRegister() as $key => $configuration) {
            if (in_array($key, $configurations)) {
                $return[] = $configuration;
            }
        }
        return $return;
    }

}