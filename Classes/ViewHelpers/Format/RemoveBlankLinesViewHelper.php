<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Format;

use HDNET\Calendarize\ViewHelpers\AbstractViewHelper;

/**
 * Class RemoveBlankLinesViewHelper.
 *
 * Removes blank lines (including spaces).
 */
class RemoveBlankLinesViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @return string
     */
    public function render()
    {
        return trim(preg_replace(
            '/[\r\n]+\s*[\r\n]+/',
            "\n",
            $this->renderChildren(),
        ));
    }
}
