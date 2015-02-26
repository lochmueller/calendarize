<?php
/**
 * Modify a DateTime
 *
 * @package Calendarize\ViewHelpers\DateTime
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Modify a DateTime@
 *
 * @author Tim Lochmüller
 */
class ModifyViewHelper extends AbstractViewHelper {

	/**
	 * Modify the given datetime by the string modification
	 *
	 * @param string    $modification
	 * @param \DateTime $dateTime
	 *
	 * @return string
	 */
	public function render($modification, \DateTime $dateTime = NULL) {
		if ($dateTime === NULL) {
			$dateTime = $this->renderChildren();
		}
		if ($dateTime instanceof \DateTime) {
			$dateTime->modify($modification);
			return '';
		}
		return '';
	}

}