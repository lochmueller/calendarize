<?php
/**
 * Uri to the list
 *
 * @package Calendarize\ViewHelpers\Link
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the list
 *
 * @author Tim Lochmüller
 */
class ListViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\ListViewHelper {

	/**
	 * Render the uri to the given list
	 *
	 * @param int $pageUid
	 *
	 * @return string
	 */
	public function render($pageUid = NULL) {
		parent::render($pageUid);
		return $this->tag->getAttribute('href');
	}
}
