<?php
/**
 * Uri to the index.
 */
namespace HDNET\Calendarize\ViewHelpers\Uri;

/**
 * Uri to the index.
 */
class IndexViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\IndexViewHelper
{
    /**
     * Render the uri to the given index.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
