<?php
/**
 * Check if a date is lower
 *
 * @package Calendarize\ViewHelpers
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers;

use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Fluid\ViewHelpers\IfViewHelper;

/**
 * Check if a date is lower
 *
 * @author Tim Lochmüller
 */
class IfDateLowerViewHelper extends IfViewHelper {

	/**
	 * @param string|\DateTime $base
	 * @param string|\DateTime $check
	 *
	 * @return string
	 */
	public function render($base, $check) {
		$base = DateTimeUtility::normalizeDateTimeSingle($base);
		$check = DateTimeUtility::normalizeDateTimeSingle($check);
		return parent::render($base > $check);
	}
}


