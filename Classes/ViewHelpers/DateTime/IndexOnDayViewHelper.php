<?php
/**
 * Check if the given Index is on the given day
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use HDNET\Calendarize\Domain\Model\Index;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Check if the given Index is on the given day
 *
 * @author Tim Lochmüller
 */
class IndexOnDayViewHelper extends AbstractViewHelper
{

    /**
     * Check if the index or one of the given indices is on the given day
     *
     * @param \DateTime $day
     * @param Index     $index
     * @param array     $indices
     *
     * @return bool
     */
    public function render(\DateTime $day, Index $index = null, $indices = array())
    {
        foreach ($indices as $idx) {
            /** @var $idx Index */
            if ($idx->getStartDate()
                    ->format('d.m.Y') === $day->format('d.m.Y')
            ) {
                return true;
            }
        }

        if ($index instanceof Index) {
            return $index->getStartDate()
                ->format('d.m.Y') === $day->format('d.m.Y');
        }
        return false;
    }

}