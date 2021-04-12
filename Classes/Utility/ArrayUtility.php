<?php

/**
 * ArrayUtility.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * ArrayUtility.
 */
class ArrayUtility
{
    /**
     * Check if the properties of the given arrays are equals.
     *
     * @param array $neededItem
     * @param array $currentItem
     * @param array $ignoredKeys
     *
     * @return bool
     */
    public static function isEqualArray(array $neededItem, array $currentItem, array $ignoredKeys = []): bool
    {
        foreach ($neededItem as $key => $value) {
            if (\in_array($key, $ignoredKeys, true)) {
                continue;
            }
            if (MathUtility::canBeInterpretedAsInteger($value)) {
                if ((int)$value !== (int)$currentItem[$key]) {
                    return false;
                }
            } elseif ((string)$value !== (string)$currentItem[$key]) {
                return false;
            }
        }

        return true;
    }
}
