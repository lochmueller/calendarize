<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Format;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * DateViewHelper.
 */
class DateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Format\DateViewHelper
{
    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('resetTimeZone', 'bool', '', false, false);
    }

    /**
     * @param array                     $arguments
     * @param \Closure                  $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     *
     * @throws Exception
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $format = $arguments['format'];
        $base = $arguments['base'] ?? GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp');
        if (\is_string($base)) {
            $base = trim($base);
        }

        if ('' === $format) {
            $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?: 'Y-m-d';
        }

        $date = $renderChildrenClosure();
        if (null === $date) {
            return '';
        }

        if (\is_string($date)) {
            $date = trim($date);
        }

        if ('' === $date) {
            $date = 'now';
        }

        if (!$date instanceof \DateTimeInterface) {
            try {
                $base = $base instanceof \DateTimeInterface ? (int)$base->format('U') : (int)strtotime((MathUtility::canBeInterpretedAsInteger($base) ? '@' : '') . $base);
                $dateTimestamp = strtotime((MathUtility::canBeInterpretedAsInteger($date) ? '@' : '') . $date, $base);
                $date = new \DateTime('@' . $dateTimestamp);
                $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            } catch (\Exception $exception) {
                throw new Exception('"' . $date . '" could not be parsed by \DateTime constructor: ' . $exception->getMessage(), 1241722579);
            }
        }

        // Add this line
        if ($arguments['resetTimeZone']) {
            $date = new \DateTime($date->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
        }

        if (false !== strpos($format, '%')) {
            // Deprecated since PHP 8.1.0
            return @strftime($format, (int)$date->format('U'));
        }

        return $date->format($format);
    }
}
