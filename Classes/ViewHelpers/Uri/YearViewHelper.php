<?php
/**
 * Uri to the year
 *
 * @package Calendarize\ViewHelpers\Link
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the year
 *
 * @author Tim Lochmüller
 */
class YearViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\YearViewHelper {

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
