<?php
/**
 * DateTime Utility
 *
 * @package Calendarize\Utility
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Utility;

use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * DateTime Utility
 *
 * @author Tim Lochmüller
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

	/**
	 * Get a normalize date time object
	 *
	 * @param int|null $day
	 * @param int|null $month
	 * @param int|null $year
	 *
	 * @return \DateTime
	 */
	static public function normalizeDateTime($day = NULL, $month = NULL, $year = NULL) {
		if (!MathUtility::canBeInterpretedAsInteger($year)) {
			$year = date('Y');
		}
		if (!MathUtility::canBeInterpretedAsInteger($month)) {
			$month = date('m');
		}
		if (!MathUtility::canBeInterpretedAsInteger($day)) {
			$day = date('d');
		}

		$date = new \DateTime();
		$date->setDate($year, $month, $day);
		$date->setTime(0, 0, 0);
		return $date;
	}
}