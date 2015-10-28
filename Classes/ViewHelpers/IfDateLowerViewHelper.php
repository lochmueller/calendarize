<?php
/**
 * Check if a date is lower
 *
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
class IfDateLowerViewHelper extends IfViewHelper
{

    /**
     * Render the view helper
     *
     * @param string|\DateTime $base
     * @param string|\DateTime $check
     *
     * @return string
     */
    public function render($base, $check)
    {
        $base = DateTimeUtility::normalizeDateTimeSingle($base);
        $check = DateTimeUtility::normalizeDateTimeSingle($check);
        // do not call parent, because the structure of the ViewHelper changed between 6.2 and 7.x
        if ($base > $check) {
            return $this->renderThenChild();
        } else {
            return $this->renderElseChild();
        }
    }
}


