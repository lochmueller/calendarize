<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Repository\IndexRepository;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class CalendarController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * Index repository
	 *
	 * @var \HDNET\Calendarize\Domain\Repository\IndexRepository
	 * @inject
	 */
	protected $indexRepository;

	/**
	 * Year action
	 */
	public function yearAction() {

		$year = 2014;



		$this->view->assign('items', $this->indexRepository->findAll());
	}

	/**
	 * Month action
	 */
	public function monthAction() {

		$this->view->assign('items', $this->indexRepository->findAll());
	}

	/**
	 * Week action
	 */
	public function weekAction() {

		$this->view->assign('items', $this->indexRepository->findAll());
	}

	/**
	 * list action
	 */
	public function listAction() {

		$this->view->assign('items', $this->indexRepository->findAll());
	}

	/**
	 * Day action
	 */
	public function dayAction() {

		$this->view->assign('items', $this->indexRepository->findAll());
	}

	/**
	 * Month action
	 */
	public function detailAction(Index $item) {

		$this->view->assign('item', $item);
	}

}
 