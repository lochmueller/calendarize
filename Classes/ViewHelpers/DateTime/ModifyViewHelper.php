<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class ModifyViewHelper extends AbstractViewHelper {

	/**
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
 