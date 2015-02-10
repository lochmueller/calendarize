<?php
/**
 * Months in year view Helper
 *
 * @package Calendarize\ViewHelpers\Loop
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Loop;

/**
 * Months in year view Helper
 *
 * @author Tim Lochmüller
 */
class MonthsInYearViewHelper extends AbstractLoopViewHelper {

	/**
	 * Get the items
	 *
	 * @param \DateTime $date
	 *
	 * @return array
	 */
	protected function getItems(\DateTime $date) {
		$months = array();
		$date->setDate($date->format('Y'), $date->format('n'), 1);
		for ($i = 0; $i < 12; $i++) {
			$months[$date->format('n')] = array(
				'week'   => $date->format('n'),
				'date'   => clone $date,
				'break3' => $date->format('n') % 3,
				'break4' => $date->format('n') % 4,
			);
			$date->modify('+1 month');
		}
		return $months;
	}
}