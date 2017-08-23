<?php
/**
 * Uri to the year.
 */
namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the year.
 */
class YearViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\YearViewHelper
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
