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
	 * Render the configuration title
	 *
	 * @param $params
	 * @param $object
	 */
	public function configurationTitle(&$params, $object) {
		$row = $params['row'];
		$params['title'] .= '<b>' . LocalizationUtility::translate('configuration.type.' . $row['type'], 'calendarize') . '</b><br />';
		switch ($row['type']) {
			case Configuration::TYPE_TIME:
				$params['title'] .= $this->getConfigurationTitleTime($row);
				break;
			case Configuration::TYPE_INCLUDE_GROUP:
			case Configuration::TYPE_EXCLUDE_GROUP:
				$params['title'] .= $this->getConfigurationGroupTitle($row);
				break;
			case Configuration::TYPE_EXTERNAL:
				$params['title'] .= 'URL: ' . $row['external_ics_url'];
				break;
		}
	}

	/**
	 * Get group title
	 *
	 * @param $row
	 *
	 * @return string
	 */
	protected function getConfigurationGroupTitle($row) {
		$title = '';
		$groups = GeneralUtility::trimExplode(',', $row['groups'], TRUE);
		if ($groups) {
			$title .= '<ul><li>' . implode('</li><li>', $groups) . '</li></ul>';
		}
		return $title;
	}

	/**
	 * Get the title for a configuration time
	 *
	 * @param $row
	 *
	 * @return string
	 */
	protected function getConfigurationTitleTime($row) {
		$title = '';
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
		} elseif ($row['start_time']) {
			$title .= '<br />' . BackendUtility::time($row['start_time'], FALSE);
			$title .= ' - ' . BackendUtility::time($row['end_time'], FALSE);
		}

		if ($row['frequency'] && $row['frequency'] !== Configuration::FREQUENCY_NONE) {
			$title .= '<br /><i>' . LocalizationUtility::translate('configuration.frequency.' . $row['frequency'], 'calendarize') . '</i>';
		}
		return $title;
	}

}