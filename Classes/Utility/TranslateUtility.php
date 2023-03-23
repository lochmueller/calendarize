<?php

/**
 * Translate helper.
 */
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
        if (\defined('TYPO3_MODE') && TYPO3 == 'FE' && !\is_object($GLOBALS['TSFE'])) {
            // check wrong eID context. Do not call "LocalizationUtility::translate" in eID context, if there is no
            // valid TypoScriptFrontendController. Skip this call by returning just the $key!
            return $key;
        }

        return (string)LocalizationUtility::translate(self::getLll($key), 'calendarize');
    }

    /**
     * Get the LLL string.
     */
    public static function getLll(string $key): string
    {
        return 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:' . $key;
    }
}
