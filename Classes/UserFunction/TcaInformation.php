<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\UserFunction;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class TcaInformation {

	/**
	 * @param $PA
	 * @param $fObj
	 *
	 * @return string
	 */
	public function informationField($PA, \TYPO3\CMS\Backend\Form\FormEngine $fObj) {
		if (!isset($PA['row']['uid'])) {
			$content = 'Please save the record first...';
		} else {
			$next = $this->getNextEvents($PA['table'], $PA['row']['uid']);
			$content = 'There are ' . $this->getIndexCount($PA['table'], $PA['row']['uid']) . ' Items in the Index of the current record. The next 5 Events are...' . $this->getEventList($next);
		}
		return '<div style="padding: 5px;">' . $content . '</div>';
	}

	protected function getEventList($events) {
		$items = array();
		foreach ($events as $event) {
			$entry = date('d.m.Y', $event['start_date']) . ' - ' . date('d.m.Y', $event['end_date']);
			if (!$event['all_day']) {
				$entry .= ' (' . BackendUtility::time($event['start_time'], FALSE) . ' - ' . BackendUtility::time($event['end_time'], FALSE) . ')';

			}
			$items[] = $entry;
		}
		return sizeof($items) ? '<ul><li>' . implode('</li><li>', $items) . '</li></ul>' : 'no Events';
	}

	/**
	 * @param $table
	 * @param $uid
	 *
	 * @return mixed
	 */
	protected function getIndexCount($table, $uid) {
		return $this->getDatabaseConnection()
			->exec_SELECTcountRows('*', 'tx_calendarize_domain_model_index', 'foreign_table="' . $table . '" AND foreign_uid=' . (int)$uid);
	}

	protected function getNextEvents($table, $uid, $limit = 5) {
		return $this->getDatabaseConnection()
			->exec_SELECTgetRows('*', 'tx_calendarize_domain_model_index', 'start_date > ' . time() . ' AND foreign_table="' . $table . '" AND foreign_uid=' . (int)$uid, '', 'start_date ASC, start_time ASC', $limit);

	}

	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

}
 