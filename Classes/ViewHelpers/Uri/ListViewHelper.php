<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

use HDNET\Calendarize\ViewHelpers\Link\ListViewHelper as BaseListViewHelper;

/**
 * Uri to the list.
 */
class ListViewHelper extends BaseListViewHelper
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
