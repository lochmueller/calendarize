<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Format;

use HDNET\Calendarize\ViewHelpers\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class LineFoldingViewHelper.
 *
 * Splits long lines (>75 characters) into multiple lines and prepends a space.
 */
class LineFoldingViewHelper extends AbstractViewHelper
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
        return preg_replace(
            // Line folding after 75 characters: RFC-5545/3-1-content-lines
            // Base on: sabre/vobject
            '/(
                 (?:^.)?         # 1 additional byte in first line because of missing single space (see next line)
                 .{74}           # 75 bytes per line (1 byte is used for a single space added after every CRLF)
                 (?![\x80-\xbf]) # prevent splitting multibyte characters
                 (?=[^\r\n])     # exclude last match in a line to prevent extra match or linebreak
             )/x',
            "$1\n ",
            $renderChildrenClosure(),
        );
    }
}
