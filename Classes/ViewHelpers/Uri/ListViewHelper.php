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
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
