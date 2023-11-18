<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * EscapeIcalTextViewHelper.
 */
class EscapeIcalTextViewHelper extends AbstractViewHelper
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

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'string', 'Value to format');
    }

    /**
     * Escapes special characters for ICalendar TEXT defined in RFC 5545 - 3.3.11.
     *
     * @return string the altered string
     *
     * @see https://tools.ietf.org/html/rfc5545#section-3.3.11
     */
    public function render(): string
    {
        $value = $this->arguments['value'];
        if (null === $value) {
            $value = $this->renderChildren();
        }

        // Note: The string syntax use double and single quotes!
        return str_replace(['\\', "\r", "\n", ',', ';'], ['\\\\', '', '\n', '\,', '\;'], $value ?? '');
    }
}
