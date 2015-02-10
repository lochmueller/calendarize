<?php
/**
 * Days in week view helper
 *
 * @package Calendarize\ViewHelpers\Loop
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Loop;

/**
 * Days in week view helper
 *
 * @author Tim Lochmüller
 */
class DaysInWeekViewHelper extends AbstractLoopViewHelper {

	/**
	 * Get items
	 *
	 * @param \DateTime $date
	 *
	 * @return array
	 */
	protected function getItems(\DateTime $date) {
		$days = array();
		$move = (int)($date->format('N') - 1);
		$date->modify('-' . $move . ' days');
		for ($i = 0; $i < 7; $i++) {
			$days[] = array(
				'day'  => $i,
				'date' => clone $date,
			);
			$date->modify('+1 day');
		}
		return $days;
	}
}
