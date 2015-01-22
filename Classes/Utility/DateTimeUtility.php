<?php
/**
 * DateTime Utility
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Utility
 * @author     Tim Lochmüller
 */

namespace HDNET\Calendarize\Utility;

/**
 * DateTime Utility
 *
 * @package    Calendarize
 * @subpackage Utility
 * @author     Tim Lochmüller
 */
class DateTimeUtility {

	/**
	 * Convert a Week/Year combination to a DateTime of the first day of week
	 *
	 * @param int $week
	 * @param int $year
	 *
	 * @return \DateTime
	 */
	static public function convertWeekYear2DayMonthYear($week, $year) {
		return new \DateTime('@' . strtotime($year . 'W' . $week . '1'));
	}
}