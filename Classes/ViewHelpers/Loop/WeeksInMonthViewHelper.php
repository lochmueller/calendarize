<?php
/**
 * Weeks in month view helper
 *
 * @package Calendarize\ViewHelpers\Loop
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Loop;

/**
 * Weeks in month view helper
 *
 * @author Tim Lochmüller
 */
class WeeksInMonthViewHelper extends AbstractLoopViewHelper {

	/**
	 * Get the items
	 *
	 * @param \DateTime $date
	 *
	 * @return array
	 */
	protected function getItems(\DateTime $date) {
		$weeks = array();
		$date->setDate($date->format('Y'), $date->format('n'), 1);
		while ((int)$date->format('t') > (int)$date->format('d')) {
			$week = (int)$date->format('W');
			if (!isset($weeks[$week])) {
				$weeks[$week] = array(
					'week' => $week,
					'date' => clone $date,
				);
			}
			$date->modify('+1 day');
		}
		return $weeks;
	}
}
