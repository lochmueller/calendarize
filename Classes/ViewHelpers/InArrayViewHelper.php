<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Checks if an element (needle) is in an array (haystack).
 */
class InArrayViewHelper extends AbstractViewHelper
{
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
    public function render()
    {
        if (!is_array($this->arguments['haystack'])) {
            return false;
        }

        return in_array($this->arguments['needle'], $this->arguments['haystack'], $this->arguments['strict']);
    }
}
