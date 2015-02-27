<?php
/**
 * Link to the index
 *
 * @package Calendarize\ViewHelpers\Link
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Link;

use HDNET\Calendarize\Domain\Model\Index;

/**
 * Link to the index
 *
 * @author Tim Lochmüller
 */
class IndexViewHelper extends AbstractLinkViewHelper {

	/**
	 * Render the link to the given index
	 *
	 * @param Index $index
	 * @param int   $pageUid
	 *
	 * @return string
	 */
	public function render(Index $index, $pageUid = NULL) {
		$additionalParams = array(
			'tx_calendarize_calendar' => array(
				'index' => $index->getUid()
			),
		);
		return parent::render($this->getPageUid($pageUid, 'detailPid'), $additionalParams);
	}
}
