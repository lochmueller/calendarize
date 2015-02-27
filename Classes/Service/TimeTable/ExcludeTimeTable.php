<?php
/**
 * Exclude service
 *
 * @package Calendarize\Service\TimeTable
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;

/**
 * Exclude service
 *
 * @author Tim Lochmüller
 */
class ExcludeTimeTable extends AbstractTimeTable {

	/**
	 * Modify the given times via the configuration
	 *
	 * @param array         $times
	 * @param Configuration $configuration
	 *
	 * @return void
	 */
	public function handleConfiguration(array &$times, Configuration $configuration) {
		$excludeTimes = array();
		foreach ($configuration->getGroups() as $group) {
			$excludeTimes = array_merge($excludeTimes, $this->buildSingleTimeTableByGroup($group));
		}
		$times = $this->checkAndRemoveTimes($times, $excludeTimes);
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

}
