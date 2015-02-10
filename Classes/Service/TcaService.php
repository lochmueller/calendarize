<?php
/**
 * TCA service
 *
 * @package Calendarize\Service
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\Configuration;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * TCA service
 *
 * @author Tim Lochmüller
 */
class TcaService extends AbstractService {

	/**
	 * Render the configuartion title
	 *
	 * @param $params
	 * @param $object
	 */
	public function configurationTitle(&$params, $object) {
		$title = '';
		$row = $params['row'];

		$title .= '<b>' . LocalizationUtility::translate('configuration.type.' . $row['type'], 'calendarize') . '</b><br />';


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
				$title .= ' ' . LocalizationUtility::translate('tx_calendarize_domain_model_index.all_day', 'calendarize');
			} else {
				if ($row['start_time']) {
					$title .= "<br />" . BackendUtility::time($row['start_time'], FALSE);
					$title .= ' - ' . BackendUtility::time($row['end_time'], FALSE);
				}
			}


			if ($row['frequency'] && $row['frequency'] !== Configuration::FREQUENCY_NONE) {
				$title .= '<br /><i>' . LocalizationUtility::translate('configuration.type.' . $row['frequency'], 'calendarize') . '</i>';
			}
		} elseif ($row['type'] === Configuration::TYPE_INCLUDE_GROUP || $row['type'] === Configuration::TYPE_EXCLUDE_GROUP) {
			$groups = GeneralUtility::trimExplode(',', $row['groups'], TRUE);
			if ($groups) {
				$title .= '<ul><li>' . implode('</li><li>', $groups) . '</li></ul>';
			}
		} elseif ($row['type'] === Configuration::TYPE_EXTERNAL) {
			$title .= 'URL: ' . $row['external_ics_url'];
		}

		$params['title'] = $title;
	}

}