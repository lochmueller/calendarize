<?php
/**
 * Days in month view helper
 *
 * @package Calendarize\ViewHelpers\Loop
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Loop;

/**
 * Days in month view helper
 *
 * @author Tim Lochmüller
 */
class DaysInMonthViewHelper extends AbstractLoopViewHelper {

	/**
	 * @param \DateTime $date
	 *
	 * @return array
	 */
	protected function getItems(\DateTime $date) {
		$weeks = array();

		// @todo implement
		
		return $weeks;
	}
}