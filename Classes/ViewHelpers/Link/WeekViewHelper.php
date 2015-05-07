<?php
/**
 * Link to the week
 *
 * @package Calendarize\ViewHelpers\Link
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the week
 *
 * @author Tim Lochmüller
 */
class WeekViewHelper extends AbstractLinkViewHelper {

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
				'year' => $date->format('Y'),
				'week' => $date->format('W'),
			),
		);
		return parent::render($this->getPageUid($pageUid), $additionalParams);
	}
}
