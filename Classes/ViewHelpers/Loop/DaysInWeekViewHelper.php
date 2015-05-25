<?php
/**
 * Days in week view helper
 *
 * @package Calendarize\ViewHelpers\Loop
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Loop;

use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Days in week view helper
 *
 * @author Tim Lochmüller
 */
class DaysInWeekViewHelper extends AbstractLoopViewHelper {

	/**
	 * The week start at
	 *
	 * @var int
	 */
	protected $weekStartsAt;

	/**
	 * Render the element
	 *
	 * @param \DateTime $date
	 * @param string    $iteration
	 * @param int       $weekStartsAt
	 *
	 * @return string
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 */
	public function render(\DateTime $date, $iteration, $weekStartsAt) {
		$this->weekStartsAt = MathUtility::forceIntegerInRange((int)$weekStartsAt, 1, 7, 1);
		return parent::render($date, $iteration);
	}

	/**
	 * Get items
	 *
	 * @param \DateTime $date
	 *
	 * @return array
	 */
	protected function getItems(\DateTime $date, $originalDate = FALSE) {
		if (!$originalDate) {
			$originalDate = clone $date;
		}
		$days = array();
		$move = (int)($date->format('N') - $this->weekStartsAt);
		$date->modify('-' . $move . ' days');
		$inWeek = FALSE;
		for ($i = 0; $i < 7; $i++) {
			$addDate = clone $date;
			if ($addDate->format('d.m.Y') == $originalDate->format('d.m.Y')) {
				$inWeek = TRUE;
			}
			$days[] = array(
				'day'  => $i,
				'date' => $addDate,
			);
			$date->modify('+1 day');
		}

		if (!$inWeek) {
			$date = clone $originalDate;
			$date->modify(($originalDate > $days[0]['date'] ? '+' : '-') . '7 days');
			return $this->getItems($date, $originalDate);
		}

		return $days;
	}
}
