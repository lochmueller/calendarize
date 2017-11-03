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
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
