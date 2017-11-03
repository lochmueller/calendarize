<?php
/**
 * Uri to the week.
 */
namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the week.
 */
class WeekViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\WeekViewHelper
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
