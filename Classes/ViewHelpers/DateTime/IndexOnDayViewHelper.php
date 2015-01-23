<?php
/**
 * Check if the given Index is on the given day
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage ViewHelpers\DateTime
 * @author     Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use HDNET\Calendarize\Domain\Model\Index;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Check if the given Index is on the given day
 *
 * @package    Calendarize
 * @subpackage ViewHelpers\DateTime
 * @author     Tim Lochmüller
 */
class IndexOnDayViewHelper extends AbstractViewHelper {

	/**
	 * @param Index     $index
	 * @param \DateTime $day
	 *
	 * @return bool
	 */
	public function render(Index $index, \DateTime $day) {
		return $index->getStartDate()
			->format('d.m.Y') === $day->format('d.m.Y');
	}

}