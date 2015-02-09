<?php
/**
 * @todo       General file information
 *
 * @package    ...
 * @author     Tim Lochmüller
 */

/**
 * @todo       General class information
 *
 * @author     Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;


use HDNET\Calendarize\Domain\Model\Configuration;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class TcaService {

	/**
	 * @param $params
	 * @param $object
	 */
	public function configurationTitle(&$params, $object) {
		$title = '';
		$row = $params['row'];

		$title .= '<b>' . ucfirst($row['type']) . '</b><br />';

		if ($row['type'] == Configuration::TYPE_TIME) {
			if ($row['start_date']) {
				$dateStart = date('d.m.Y', $row['start_date']);
				$dateEnd = date('d.m.Y', $row['end_date']);
				$title .= $dateStart;
				if ($dateStart != $dateEnd) {
					$title .= ' - ' . $dateEnd;
				}
			}
			if ($row['all_day']) {
				$title .= ' All Day';
			} else {
				if ($row['start_time']) {
					$title .= "<br />" . BackendUtility::time($row['start_time'], FALSE);
					$title .= ' - ' . BackendUtility::time($row['end_time'], FALSE);
				}
			}
			if ($row['frequency'] && $row['frequency'] !== Configuration::FREQUENCY_NONE) {
				$title .= '<br /><i>' . ucfirst($row['frequency']) . '</i>';
			}
		} elseif ($row['type'] === Configuration::TYPE_INCLUDE_GROUP || $row['type'] === Configuration::TYPE_EXCLUDE_GROUP) {
			$title .= 'Groups: ' . $row['groups'];
		}

		$params['title'] = $title;
	}

}