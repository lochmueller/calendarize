<?php
/**
 * Link to the index
 *
 * @package Calendarize\ViewHelpers\Link
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Link;

use HDNET\Calendarize\Domain\Model\Index;
use TYPO3\CMS\Fluid\ViewHelpers\Link\PageViewHelper;

/**
 * Link to the index
 *
 * @author Tim Lochmüller
 */
class IndexViewHelper extends PageViewHelper {

	/**
	 * Render the link to the given index
	 *
	 * @param int   $pageUid
	 * @param Index $index
	 *
	 * @return string
	 */
	public function render($pageUid, Index $index) {
		$additionalParams = array(
			'tx_calendarize_calendar' => array(
				'index' => $index->getUid()
			),
		);
		return parent::render($pageUid, $additionalParams);
	}
}
