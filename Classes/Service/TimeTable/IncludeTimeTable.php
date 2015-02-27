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
class IncludeTimeTable extends AbstractTimeTable {

	/**
	 * Modify the given times via the configuration
	 *
	 * @param array         $times
	 * @param Configuration $configuration
	 *
	 * @return void
	 */
	public function handleConfiguration(array &$times, Configuration $configuration) {
		foreach ($configuration->getGroups() as $group) {
			$times = array_merge($times, $this->buildSingleTimeTableByGroup($group));
		}
	}

}
