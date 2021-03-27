<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class RemoveBlankLinesViewHelper.
 *
 * Removes blank lines (including spaces).
 */
class RemoveBlankLinesViewHelper extends \HDNET\Calendarize\ViewHelpers\AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @param array                     $arguments
     * @param \Closure                  $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        return trim(preg_replace(
            '/[\r\n]+\s*[\r\n]+/',
            "\n",
            $renderChildrenClosure()
        ));
    }
}
