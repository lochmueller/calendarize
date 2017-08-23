<?php
/**
 * Uri to the list.
 */
namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the list.
 */
class ListViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\ListViewHelper
{
    /**
     * Render the uri to the given list.
     *
     * @param int $pageUid
     *
     * @return string
     */
    public function render($pageUid = null)
    {
        parent::render($pageUid);

        return $this->lastHref;
    }
}
