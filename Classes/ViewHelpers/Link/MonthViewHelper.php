<?php
/**
 * Link to the month
 *
 * @package Calendarize\ViewHelpers\Link
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Link;

use TYPO3\CMS\Fluid\ViewHelpers\Link\PageViewHelper;

/**
 * Link to the month
 *
 * @author Tim Lochmüller
 */
class MonthViewHelper extends PageViewHelper {

	/**
	 * Render the link to the given day
	 *
	 * @param int       $pageUid
	 * @param \DateTime $date
	 *
	 * @return string
	 */
	public function render($pageUid, \DateTime $date) {
		$additionalParams = array(
			'tx_calendarize_calendar' => array(
				'year'  => $date->format('Y'),
				'month' => $date->format('n'),
			),
		);
		return parent::render($pageUid, $additionalParams);
	}
}
