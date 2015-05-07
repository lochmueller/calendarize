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
		return new \DateTime('@' . strtotime($year . 'W' . $week . '1'), self::getTimeZone());
	}

	/**
	 * Time zone is set by the TYPO3 core
	 *
	 * @return \DateTimeZone
	 * @see \TYPO3\CMS\Core\Core\Bootstrap->setDefaultTimezone()
	 */
	static public function getTimeZone() {
		return new \DateTimeZone(date_default_timezone_get());
	}

	/**
	 * Get the time seconds of the given date (TYPO3 Backend style)
	 *
	 * @param \DateTime $dateTime
	 *
	 * @return int
	 */
	static public function getDaySecondsOfDateTime(\DateTime $dateTime) {
		$hours = (int)$dateTime->format('G');
		$minutes = $hours * 60 + (int)$dateTime->format('i');
		return $minutes * 60 + (int)$dateTime->format('s');
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

		$date = new \DateTime('now', DateTimeUtility::getTimeZone());
		$date->setDate($year, $month, $day);
		$date->setTime(0, 0, 0);
		return $date;
	}

	/**
	 * Get a normalize date time object
	 *
	 * @param int|null|\DateTime $dateTimeOrString
	 *
	 * @return \DateTime
	 */
	static public function normalizeDateTimeSingle($dateTimeOrString) {
		if ($dateTimeOrString instanceof \DateTime) {
			return $dateTimeOrString;
		} elseif (!is_string($dateTimeOrString)) {
			$dateTimeOrString = 'now';
		}
		return new \DateTime($dateTimeOrString, DateTimeUtility::getTimeZone());
	}
}