<?php
/**
 * TCA information
 *
 * @package Calendarize\UserFunction
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\UserFunction;

use TYPO3\CMS\Backend\Form\FormEngine;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * TCA information
 *
 * @author Tim Lochmüller
 */
class TcaInformation {

	/**
	 * Generate the information field
	 *
	 * @param array      $configuration
	 * @param FormEngine $fObj
	 *
	 * @return string
	 */
	public function informationField($configuration, FormEngine $fObj) {
		if (!isset($configuration['row']['uid'])) {
			$content = LocalizationUtility::translate('save.first', 'calendarize');
		} else {
			// @todo l10n
			$next = $this->getNextEvents($configuration['table'], $configuration['row']['uid']);
			$content = 'There are ' . $this->getIndexCount($configuration['table'], $configuration['row']['uid']) . ' Items in the Index of the current record. The next 5 Events are...' . $this->getEventList($next);
		}
		return '<div style="padding: 5px;">' . $content . '</div>';
	}

	/**
	 * Get event list
	 *
	 * @param $events
	 *
	 * @return string
	 */
	protected function getEventList($events) {
		$items = array();
		foreach ($events as $event) {
			$entry = date('d.m.Y', $event['start_date']) . ' - ' . date('d.m.Y', $event['end_date']);
			if (!$event['all_day']) {
				$start = BackendUtility::time($event['start_time'], FALSE);
				$end = BackendUtility::time($event['end_time'], FALSE);
				$entry .= ' (' . $start . ' - ' . $end . ')';

			}
			$items[] = $entry;
		}
		return sizeof($items) ? '<ul><li>' . implode('</li><li>', $items) . '</li></ul>' : 'no Events';
	}

	/**
	 * Get index count
	 *
	 * @param $table
	 * @param $uid
	 *
	 * @return mixed
	 */
	protected function getIndexCount($table, $uid) {
		return $this->getDatabaseConnection()
			->exec_SELECTcountRows('*', 'tx_calendarize_domain_model_index', 'foreign_table="' . $table . '" AND foreign_uid=' . (int)$uid);
	}

	/**
	 * Get the next events
	 *
	 * @param string $table
	 * @param int    $uid
	 * @param int    $limit
	 *
	 * @return array|NULL
	 */
	protected function getNextEvents($table, $uid, $limit = 5) {
		return $this->getDatabaseConnection()
			->exec_SELECTgetRows('*', 'tx_calendarize_domain_model_index', 'start_date > ' . time() . ' AND foreign_table="' . $table . '" AND foreign_uid=' . (int)$uid, '', 'start_date ASC, start_time ASC', $limit);

	}

	/**
	 * Get database connection
	 *
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

}