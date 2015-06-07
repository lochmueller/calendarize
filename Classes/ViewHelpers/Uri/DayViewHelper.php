<?php
/**
 * Uri to the day
 *
 * @package Calendarize\ViewHelpers\Link
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the day
 *
 * @author Tim Lochmüller
 */
class DayViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\DayViewHelper {

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
