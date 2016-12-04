<?php
/**
 * Check if the given Index is on the given day
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\IndexUtility;
use HDNET\Calendarize\ViewHelpers\AbstractViewHelper;

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
     * @param string    $modification
     *
     * @return bool
     */
    public function render(\DateTime $day, Index $index = null, $indices = [], $modification = '')
    {
        $day = DateTimeUtility::normalizeDateTimeSingle($day->format('d.m.Y'));
        $baseDay = clone $day;
        if ($modification != '') {
            $baseDay->modify($modification);
        }

        $baseDay->setTime(0, 0, 0);
        $startTime = clone $baseDay;
        $baseDay->setTime(23, 59, 59);
        $endTime = clone $baseDay;

        if ($index instanceof Index) {
            $indices[] = $index;
        }
        foreach ($indices as $index) {
            /** @var $index Index */
            if (IndexUtility::isIndexInRange($index, $startTime, $endTime)) {
                return true;
            }
        }

        return false;
    }
}
