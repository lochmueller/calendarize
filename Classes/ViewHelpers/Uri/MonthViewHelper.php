<?php
/**
 * Uri to the month
 *
 */
namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the month
 */
class MonthViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\MonthViewHelper
{

    /**
     * Render the uri to the given day
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
