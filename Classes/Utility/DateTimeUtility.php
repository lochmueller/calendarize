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
	 * @param int $week
	 * @param int $year
	 *
	 * @return \DateTime
	 */
	static public function convertWeekYear2DayMonthYear($week, $year) {

		return new \DateTime('now');
	}
}
 