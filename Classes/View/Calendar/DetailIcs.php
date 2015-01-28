<?php
/**
 * ICS View
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\View\Calendar;

use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * ICS View
 *
 * @author Tim Lochmüller
 */
class DetailIcs extends TemplateView {

	/**
	 * @param null $actionName
	 *
	 * @return void
	 */
	public function render($actionName = NULL) {
		$content = parent::render($actionName);
		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: inline; filename=ical.ics');
		echo $content;
		die();
	}

}


