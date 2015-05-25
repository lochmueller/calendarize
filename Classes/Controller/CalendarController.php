<?php
/**
 * Calendar
 *
 * @package Calendarize\Controller
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
use TYPO3\CMS\Extensionmanager\Controller\ActionController;

/**
 * Calendar
 *
 * @author Tim Lochmüller
 */
class CalendarController extends ActionController {

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
	public function initializeAction() {
		$this->indexRepository->setIndexTypes(GeneralUtility::trimExplode(',', $this->settings['configuration']));

		if (isset($this->arguments['startDate'])) {
			$this->arguments['startDate']->getPropertyMappingConfiguration()
				->setTypeConverterOption('TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\DateTimeConverter', DateTimeConverter::CONFIGURATION_DATE_FORMAT, $this->settings['dateFormat']);
		}
		if (isset($this->arguments['endDate'])) {
			$this->arguments['endDate']->getPropertyMappingConfiguration()
				->setTypeConverterOption('TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\DateTimeConverter', DateTimeConverter::CONFIGURATION_DATE_FORMAT, $this->settings['dateFormat']);
		}
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
	public function listAction(Index $index = NULL, \DateTime $startDate = NULL, \DateTime $endDate = NULL, array $customSearch = array(), $year = NULL, $month = NULL, $week = NULL) {
		if (($index instanceof Index) && in_array('detail', $this->getAllowedActions())) {
			$this->forward('detail');
		}

		$searchMode = FALSE;
		if ($startDate || $endDate || $customSearch) {
			$searchMode = TRUE;
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

		$this->view->assignMultiple(array(
			'indices'    => $indices,
			'searchMode' => $searchMode
		));
	}

	/**
	 * Year action
	 *
	 * @param int $year
	 *
	 * @return void
	 */
	public function yearAction($year = NULL) {
		$date = DateTimeUtility::normalizeDateTime(1, 1, $year);

		$this->view->assign('indices', $this->indexRepository->findYear($year));
		$this->view->assign('date', $date);
	}

	/**
	 * Month action
	 *
	 * @param int $year
	 * @param int $month
	 *
	 * @return void
	 */
	public function monthAction($year = NULL, $month = NULL) {
		$date = DateTimeUtility::normalizeDateTime(1, $month, $year);

		$this->view->assignMultiple(array(
			'date'    => $date,
			'indices' => $this->indexRepository->findMonth($date->format('Y'), $date->format('n')),
		));
	}

	/**
	 * Week action
	 *
	 * @param int $year
	 * @param int $week
	 *
	 * @return void
	 */
	public function weekAction($year = NULL, $week = NULL) {
		if ($year === NULL) {
			$year = date('Y');
		}
		if ($week === NULL) {
			$week = date('W');
		}
		$firstDay = DateTimeUtility::convertWeekYear2DayMonthYear($week, $year);
		$firstDay->setTime(0, 0, 0);
		$this->view->assignMultiple(array(
			'firstDay' => $firstDay,
			'indices'  => $this->indexRepository->findWeek($year, $week),
		));
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
	public function dayAction($year = NULL, $month = NULL, $day = NULL) {
		$today = DateTimeUtility::normalizeDateTime($day, $month, $year);
		$today->modify('+12 hours');

		$previous = clone $today;
		$previous->modify('-1 day');

		$next = clone $today;
		$next->modify('+1 day');

		$this->view->assignMultiple(array(
			'indices'  => $this->indexRepository->findDay($year, $month, $day),
			'today'    => $today,
			'previous' => $previous,
			'next'     => $next,
		));
	}

	/**
	 * Detail action
	 *
	 * @param \HDNET\Calendarize\Domain\Model\Index $index
	 *
	 * @return string
	 */
	public function detailAction(Index $index = NULL) {
		if ($index === NULL) {
			if (!MathUtility::canBeInterpretedAsInteger($this->settings['listPid'])) {
				return LocalizationUtility::translate('noEventDetailView', 'calendarize');
			}
			$this->redirect('list', NULL, NULL, array(), NULL, $this->settings['listPid'], 301);
		}
		$this->view->assign('index', $index);
		// domain for ICS view
		$this->view->assign('domain', GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'));
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
	public function searchAction(\DateTime $startDate = NULL, \DateTime $endDate = NULL, array $customSearch = array()) {
		if (!($startDate instanceof \DateTime)) {
			$startDate = new \DateTime('now', DateTimeUtility::getTimeZone());
		}
		if (!($endDate instanceof \DateTime)) {
			$endDate = new \DateTime('+1 month', DateTimeUtility::getTimeZone());
		}

		$this->view->assignMultiple(array(
			'startDate'      => $startDate,
			'endDate'        => $endDate,
			'customSearch'   => $customSearch,
			'configurations' => $this->getCurrentConfigurations()
		));

	}

	/**
	 * Get the allowed actions
	 *
	 * @return array
	 */
	protected function getAllowedActions() {
		$configuration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$allowedControllerActions = array();
		foreach ($configuration['controllerConfiguration'] as $controllerName => $controllerActions) {
			$allowedControllerActions[$controllerName] = $controllerActions['actions'];
		}
		return isset($allowedControllerActions['Calendar']) ? $allowedControllerActions['Calendar'] : array();
	}

	/**
	 * Get the current configurations
	 *
	 * @return array
	 */
	protected function getCurrentConfigurations() {
		$configurations = GeneralUtility::trimExplode(',', $this->settings['configuration'], TRUE);
		$return = array();
		foreach (Register::getRegister() as $key => $configuration) {
			if (in_array($key, $configurations)) {
				$return[] = $configuration;
			}
		}
		return $return;
	}

}