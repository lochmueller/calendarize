<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * EscapeNewLinesViewHelper.
 */
class EscapeNewLinesViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * Disable the output escaping interceptor so that the value is not htmlspecialchar'd twice.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'string', 'Value to format');
    }

    /**
     * Escapes special characters with their escaped counterparts as needed using PHPs htmlspecialchars() function.
     *
     * @return string the altered string
     *
     * @see http://www.php.net/manual/function.htmlspecialchars.php
     *
     * @api
     */
    public function render()
    {
        $value = $this->arguments['value'];
        if (null === $value) {
            $value = $this->renderChildren();
        }

        return str_replace(["\n", "\r"], ['\\n', '\\r'], $value);
    }
}
