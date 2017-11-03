<?php
/**
 * Uri to the month.
 */
namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the month.
 */
class MonthViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\MonthViewHelper
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
