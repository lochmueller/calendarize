<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Formats the date to UTC.
 */
class FormatUtcDateViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * Needed as child node's output can return a DateTime object which can't be escaped.
     *
     * @var bool
     */
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'date',
            'mixed',
            'Either an object implementing DateTimeInterface or a string that is accepted by DateTime constructor'
        );
        $this->registerArgument(
            'format',
            'string',
            'Format String which is taken to format the Date/Time',
            false,
            ''
        );
        $this->registerArgument(
            'base',
            'mixed',
            'A base time (an object implementing DateTimeInterface or a string) used if $date is a relative
             date specification. Defaults to current time.'
        );
    }

    /**
     * Format dateTime to the UTC timezone.
     *
     * @return string
     *
     * @throws Exception
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        // save configured timezone
        $timezone = date_default_timezone_get();
        // set timezone to UTC
        date_default_timezone_set('UTC');

        $date = $arguments['date'];
        if ($date instanceof \DateTimeInterface) {
            $renderChildrenClosure = static function () use ($date) {
                // Convert date to timestamp, so that it can be reparsed.
                return $date->getTimestamp();
            };
        }

        $result = parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);

        // restore timezone setting
        date_default_timezone_set($timezone);

        return $result;
    }
}
