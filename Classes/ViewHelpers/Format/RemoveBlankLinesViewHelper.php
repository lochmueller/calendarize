<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Format;

use HDNET\Calendarize\ViewHelpers\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

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
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
    ) {
        return trim(preg_replace(
            '/[\r\n]+\s*[\r\n]+/',
            "\n",
            $renderChildrenClosure(),
        ));
    }
}
