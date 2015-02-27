<?php
/**
 * Link to the list
 *
 * @package Calendarize\ViewHelpers\Link
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the list
 *
 * @author Tim Lochmüller
 */
class ListViewHelper extends AbstractLinkViewHelper {

	/**
	 * Render the link to the given list
	 *
	 * @param int $pageUid
	 *
	 * @return string
	 */
	public function render($pageUid = NULL) {
		return parent::render($this->getPageUid($pageUid, 'listPid'));
	}
}
