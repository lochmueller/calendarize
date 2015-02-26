<?php
/**
 * ICS View
 *
 * @package Calendarize\View\Calendar
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
	 * Render the given template as ICS file
	 *
	 * @param string $actionName
	 *
	 * @return void
	 */
	public function render($actionName = NULL) {
		$content = parent::render($actionName);
		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: inline; filename=event.ics');
		echo $content;
		die();
	}

}


