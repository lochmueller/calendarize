<?php
/**
 * Uri to the month
 *
 * @package Calendarize\ViewHelpers\Link
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the month
 *
 * @author Tim Lochmüller
 */
class MonthViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\MonthViewHelper {

	/**
	 * Render the uri to the given day
	 *
	 * @param \DateTime $date
	 * @param int       $pageUid
	 *
	 * @return string
	 */
	public function render(\DateTime $date, $pageUid = NULL) {
		parent::render($date, $pageUid);
		return $this->tag->getAttribute('href');
	}
}
