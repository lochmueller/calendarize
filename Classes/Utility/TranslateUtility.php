<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Translate helper.
 */
class TranslateUtility
{
    /**
     * Set the right path and extension for translations in PHP.
     */
    public static function get(string $key): string
    {
        return LocalizationUtility::translate(self::getLll($key), 'calendarize') ?? $key;
    }

    /**
     * Get the LLL string.
     */
    public static function getLll(string $key): string
    {
        return 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:' . $key;
    }
}
