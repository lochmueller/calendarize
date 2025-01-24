<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Format;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * DateViewHelper.
 */
class DateViewHelper extends AbstractViewHelper
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
            'Either an object implementing DateTimeInterface or a string that
             is accepted by DateTime constructor',
        );
        $this->registerArgument(
            'format',
            'string',
            'Format String which is taken to format the Date/Time',
            false,
            '',
        );
        $this->registerArgument(
            'base',
            'mixed',
            'A base time (an object implementing DateTimeInterface or a string) used if $date is a relative
             date specification. Defaults to current time.',
        );
        $this->registerArgument(
            'resetTimeZone',
            'bool',
            '',
        );
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
    ) {
        $format = $arguments['format'];
        $base = $arguments['base'] ?? GeneralUtility::makeInstance(Context::class)
            ->getPropertyFromAspect('date', 'timestamp');
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
                $base = $base instanceof \DateTimeInterface
                    ? (int)$base->format('U')
                    : (int)strtotime((MathUtility::canBeInterpretedAsInteger($base) ? '@' : '') . $base);
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

        if (str_contains($format, '%')) {
            // Deprecated since PHP 8.1.0
            return @strftime($format, (int)$date->format('U'));
        }

        return $date->format($format);
    }
}
