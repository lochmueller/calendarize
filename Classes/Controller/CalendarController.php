<?php
/**
 * Calendar
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Controller
 * @author     Tim Lochmüller
 */

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extensionmanager\Controller\ActionController;

/**
 * Calendar
 *
 * @package    Calendarize
 * @subpackage Controller
 * @author     Tim Lochmüller
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
	 * List action
	 *
	 * @return void
	 */
	public function listAction() {
		$this->view->assign('indices', $this->indexRepository->findList((int)$this->settings['limit']));
	}

	/**
	 * Year action
	 *
	 * @param int $year
	 *
	 * @return void
	 */
	public function yearAction($year = NULL) {
		if ($year === NULL) {
			$year = date('Y');
		}
		$date = new \DateTime();
		$date->setDate($year, 1, 1);

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
		if ($year === NULL) {
			$year = date('Y');
		}
		if ($month === NULL) {
			$month = date('m');
		}

		$date = new \DateTime();
		$date->setDate($year, $month, 1);

		$nextMonth = clone $date;
		$nextMonth->modify('+1 month');
		$lastMonth = clone $date;
		$lastMonth->modify('-1 month');

		$this->view->assignMultiple(array(
			'date'      => $date,
			'indices'   => $this->indexRepository->findMonth($year, $month),
			'nextMonth' => array(
				'year'  => $nextMonth->format('Y'),
				'month' => $nextMonth->format('n')
			),
			'lastMonth' => array(
				'year'  => $lastMonth->format('Y'),
				'month' => $lastMonth->format('n')
			),
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
		if ($year === NULL) {
			$year = date('Y');
		}
		if ($month === NULL) {
			$month = date('m');
		}
		if ($day === NULL) {
			$day = date('d');
		}
		$todayTimestamp = mktime(12, 0, 0, $month, $day, $year);
		$today = new \DateTime($todayTimestamp);
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
				return 'No Event found! There is no valid fallback PID (list) in the detail plugin';
			}
			$this->redirect('list', NULL, NULL, array(), NULL, $this->settings['listPid'], 301);
		}
		$this->view->assign('index', $index);
		// domain for ICS view
		$this->view->assign('domain', GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'));
		return $this->view->render();
	}

}