<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Uri;

use HDNET\Calendarize\ViewHelpers\Link\IndexViewHelper as BaseIndexViewHelper;

/**
 * Uri to the index.
 */
class IndexViewHelper extends BaseIndexViewHelper
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
