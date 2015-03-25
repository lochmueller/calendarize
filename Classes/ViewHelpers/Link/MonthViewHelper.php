<?php
/**
 * Link to the month
 *
 * @package Calendarize\ViewHelpers\Link
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the month
 *
 * @author Tim Lochmüller
 */
class MonthViewHelper extends AbstractLinkViewHelper {

	/**
	 * Render the link to the given day
	 *
	 * @param \DateTime $date
	 * @param int       $pageUid
	 *
	 * @return string
	 */
	public function render(\DateTime $date, $pageUid = NULL) {
		$additionalParams = array(
			'tx_calendarize_calendar' => array(
				'year'  => $date->format('Y'),
				'month' => $date->format('n'),
			),
		);
		return parent::render($this->getPageUid($pageUid), $additionalParams);
	}
}
