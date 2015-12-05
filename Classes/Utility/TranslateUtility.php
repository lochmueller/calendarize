<?php
/**
 * Translate helper
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Utility;


use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Translate helper
 */
class TranslateUtility
{

    /**
     * Set the right path and extension for translations in PHP
     *
     * @param string $key
     *
     * @return NULL|string
     */
    static public function get($key)
    {
        return LocalizationUtility::translate(self::getLll($key), 'calendarize');
    }

    /**
     * Get the LLL string
     *
     * @param string $key
     *
     * @return string
     */
    static public function getLll($key)
    {
        return \HDNET\Autoloader\Utility\TranslateUtility::getLllString($key, 'calendarize', 'locallang.xlf');
    }

}