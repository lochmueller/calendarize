<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use HDNET\Calendarize\ViewHelpers\Format\DateViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Formats the date to UTC.
 */
class FormatUtcDateViewHelper extends DateViewHelper
{
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
        RenderingContextInterface $renderingContext,
    ): string {
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
