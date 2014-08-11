<?php
/**
 * Time table builder service
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Service
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Service;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Time table builder service
 *
 * @package    Calendarize
 * @subpackage Service
 * @author     Tim Lochmüller
 */
class TimeTableService {

	/**
	 * Build the timetable for the given configuration matrix (sorted)
	 *
	 * @param array $uids
	 *
	 * @return array
	 */
	public function getTimeTablesByConfigurationUids(array $uids) {
		$timeTables = array();
		if (!$uids) {
			return $timeTables;
		}
		return $timeTables;

		foreach ($uids as $configurationUid) {
			DebuggerUtility::var_dump($this->buildSingleTimeTable($configurationUid));
		}

		DebuggerUtility::var_dump($uids);
		die();
		#$timeTable =

		return $timeTables;
	}

	/**
	 * Build time table by configuration uid
	 *
	 * @param $configurationUid
	 *
	 * @return array
	 */
	protected function buildSingleTimeTable($configurationUid) {
		$timeTable = array();

		/** @var \HDNET\Calendarize\Domain\Repository\ConfigurationRepository $configRepository */
		$configRepository = HelperUtility::create('HDNET\\Calendarize\\Domain\\Repository\\ConfigurationRepository');
		$configuration = $configRepository->findByUid($configurationUid);
		if (!($configuration instanceof Configuration)) {
			return $timeTable;
		}

		if ($configuration->getType() == Configuration::TYPE_TIME) {
			$entry = array(
				'start_date' => $configuration->getStartDate(),
				'end_date'   => $configuration->getEndDate(),
				'start_time' => $configuration->getStartTime(),
				'end_time'   => $configuration->getEndTime(),
				'all_day'    => $configuration->getAllDay(),
			);
			$timeTable[] = $entry;
		}

		return $timeTable;
	}

}
 