<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Checks if an element (needle) is in an array (haystack).
 */
class InArrayViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('needle', 'mixed', 'The searched value.', true);
        $this->registerArgument('haystack', 'array', 'The array.', true);
        $this->registerArgument(
            'strict',
            'boolean',
            'If true then the function will also check the types.',
            false,
            false,
        );
    }

    /**
     * @return bool
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
    ) {
        if (!\is_array($arguments['haystack'])) {
            return false;
        }

        return \in_array($arguments['needle'], $arguments['haystack'], $arguments['strict']);
    }
}
