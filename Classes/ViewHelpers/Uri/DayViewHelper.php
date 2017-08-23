<?php
/**
 * Uri to the day.
 */

namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the day.
 */
class DayViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\DayViewHelper
{
    /**
     * Render the uri to the given day.
     *
     * @param \DateTime $date
     * @param int       $pageUid
     *
     * @return string
     */
    public function render(\DateTime $date, $pageUid = null)
    {
        parent::render($date, $pageUid);

        return $this->lastHref;
    }
}
