<?php
/**
 * Uri to the index
 *
 * @package Calendarize\ViewHelpers\Uri
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Uri;

use HDNET\Calendarize\Domain\Model\Index;

/**
 * Uri to the index
 *
 * @author Tim Lochmüller
 */
class IndexViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\IndexViewHelper {

	/**
	 * Render the uri to the given index
	 *
	 * @param Index $index
	 * @param int   $pageUid
	 *
	 * @return string
	 */
	public function render(Index $index, $pageUid = NULL) {
		parent::render($index, $pageUid);
		return $this->tag->getAttribute('href');
	}
}
