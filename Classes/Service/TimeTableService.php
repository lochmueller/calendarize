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

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationGroup;
use HDNET\Calendarize\Utility\HelperUtility;

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
	 * @param array $ids
	 *
	 * @return array
	 */
	public function getTimeTablesByConfigurationIds(array $ids) {
		$timeTable = array();
		if (!$ids) {
			return $timeTable;
		}

		/** @var \HDNET\Calendarize\Domain\Repository\ConfigurationRepository $configRepository */
		$configRepository = HelperUtility::create('HDNET\\Calendarize\\Domain\\Repository\\ConfigurationRepository');

		foreach ($ids as $configurationUid) {
			$configuration = $configRepository->findByUid($configurationUid);
			if (!($configuration instanceof Configuration)) {
				continue;
			}

			$singleTimeTable = $this->buildSingleTimeTable($configuration);
			if ($configuration->getType() == Configuration::TYPE_EXCLUDE_GROUP) {
				$timeTable = $this->checkAndRemoveTimes($timeTable, $singleTimeTable);
			} else {
				$timeTable = array_merge($timeTable, $singleTimeTable);
			}
		}

		return $timeTable;
	}

	/**
	 * Remove excluded events
	 *
	 * @param $base
	 * @param $remove
	 *
	 * @return mixed
	 */
	protected function checkAndRemoveTimes($base, $remove) {
		foreach ($base as $key => $value) {
			foreach ($remove as $removeValue) {
				$eventStart = &$value['start_date'];
				$eventEnd = &$value['end_date'];
				$removeStart = &$removeValue['start_date'];
				$removeEnd = &$removeValue['end_date'];

				$startIn = ($eventStart >= $removeStart && $eventStart < $removeEnd);
				$endIn = ($eventEnd > $removeStart && $eventEnd <= $removeEnd);
				$envelope = ($eventStart < $removeStart && $eventEnd > $removeEnd);

				if ($startIn || $endIn || $envelope) {
					unset($base[$key]);
					continue;
				}
			}
		}

		return $base;
	}

	/**
	 * Build time table by configuration uid
	 *
	 * @param Configuration $configuration
	 *
	 * @return array
	 */
	protected function buildSingleTimeTable(Configuration $configuration) {
		$timeTable = array();
		if ($configuration->getType() == Configuration::TYPE_TIME) {
			$baseEntry = array(
				'start_date' => $configuration->getStartDate(),
				'end_date'   => $configuration->getEndDate(),
				'start_time' => $configuration->getAllDay() ? NULL : $configuration->getStartTime(),
				'end_time'   => $configuration->getAllDay() ? NULL : $configuration->getEndTime(),
				'all_day'    => $configuration->getAllDay(),
			);
			$timeTable[] = $baseEntry;
			$this->addFrequencyItems($timeTable, $configuration, $baseEntry);

		} else if ($configuration->getType() === Configuration::TYPE_EXCLUDE_GROUP || $configuration->getType() === Configuration::TYPE_INCLUDE_GROUP) {
			foreach ($configuration->getGroups() as $group) {
				$timeTable = array_merge($timeTable, $this->buildSingleTimeTableByGroup($group));
			}
		}

		return $timeTable;
	}

	/**
	 * Add frequency items
	 *
	 * @param array         $timeTable
	 * @param Configuration $configuration
	 * @param array         $baseEntry
	 */
	protected function addFrequencyItems(array &$timeTable, Configuration $configuration, array $baseEntry) {
		$frequencyIncrement = $this->getFrequencyIncrement($configuration);
		if ($frequencyIncrement) {
			$amountCounter = $configuration->getCounterAmount();
			$tillDate = $configuration->getTillDate();
			$maxLimit = 99999;
			$lastLoop = $baseEntry;
			for ($i = 0; $i < $maxLimit && ($amountCounter === 0 || $i < $amountCounter); $i++) {
				$loopEntry = $lastLoop;

				/** @var $startDate \DateTime */
				$startDate = clone $loopEntry['start_date'];
				$startDate->modify($frequencyIncrement);
				$loopEntry['start_date'] = $startDate;

				/** @var $endDate \DateTime */
				$endDate = clone $loopEntry['end_date'];
				$endDate->modify($frequencyIncrement);
				$loopEntry['end_date'] = $endDate;

				if ($tillDate instanceof \DateTime && $loopEntry['start_date'] > $tillDate) {
					break;
				}

				$lastLoop = $loopEntry;
				$timeTable[] = $loopEntry;
			}
		}
	}

	/**
	 * Get the frequency date increment
	 *
	 * @param Configuration $configuration
	 *
	 * @return null|string
	 */
	protected function getFrequencyIncrement(Configuration $configuration) {
		$interval = $configuration->getCounterInterval() <= 1 ? 1 : $configuration->getCounterInterval();
		switch ($configuration->getFrequency()) {
			case Configuration::FREQUENCY_DAILY:
				return '+' . $interval . ' days';
			case Configuration::FREQUENCY_WEEKLY:
				return '+' . $interval . ' weeks';
			case Configuration::FREQUENCY_MONTHLY:
				return '+' . $interval . ' months';
			case Configuration::FREQUENCY_YEARLY:
				return '+' . $interval . ' years';
		}
		return NULL;
	}

	/**
	 * @param ConfigurationGroup $group
	 *
	 * @return array
	 */
	protected function buildSingleTimeTableByGroup(ConfigurationGroup $group) {
		$ids = array();
		foreach ($group->getConfigurations() as $configuration) {
			if ($configuration instanceof Configuration) {
				$ids[] = $configuration->getUid();
			}
		}
		return $this->getTimeTablesByConfigurationIds($ids);
	}

}