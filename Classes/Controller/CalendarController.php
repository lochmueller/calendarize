<?php
/**
 * Calendar
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Controller
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Controller;

use TYPO3\CMS\Extensionmanager\Controller\ActionController;

/**
 * Calendar
 *
 * @package    Calendarize
 * @subpackage Controller
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
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
	 */
	public function listAction() {
		$this->view->assign('indices', $this->indexRepository->findList());
	}

	/**
	 * Year action
	 */
	public function yearAction() {
		$this->view->assign('indices', $this->indexRepository->findYear());

	}

	/**
	 * Month action
	 */
	public function monthAction() {
		$this->view->assign('indices', $this->indexRepository->findMonth());

	}

	/**
	 * Week action
	 */
	public function weekAction() {
		$this->view->assign('indices', $this->indexRepository->findYear());

	}

	/**
	 * Day action
	 */
	public function dayAction() {
		$this->view->assign('indices', $this->indexRepository->findDay());
	}

	/**
	 * Detail action
	 *
	 * @param \HDNET\Calendarize\Domain\Model\Index $index
	 */
	public function detailAction(\HDNET\Calendarize\Domain\Model\Index $index) {
		$this->view->assign('index', $index);
	}

}
 