<?php
/**
 * Check if a date is upper
 *
 * @package Calendarize\ViewHelpers
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers;

use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Fluid\ViewHelpers\IfViewHelper;

/**
 * Check if a date is upper
 *
 * @author Tim Lochmüller
 */
class IfDateUpperViewHelper extends IfViewHelper {

	/**
	 * @param string|\DateTime $base
	 * @param string|\DateTime $check
	 *
	 * @return string
	 */
	public function render($base, $check) {
		$base = DateTimeUtility::normalizeDateTimeSingle($base);
		$check = DateTimeUtility::normalizeDateTimeSingle($check);
		return parent::render($base < $check);
	}
}


