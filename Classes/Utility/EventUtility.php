<?php
/**
 * Event utility
 *
 * @author  Carsten Biebricher
 */

namespace HDNET\Calendarize\Utility;

use HDNET\Calendarize\Utility\HelperUtility;

/**
 * Event utility
 */
class EventUtility
{
    /**
     * Get the original record by configuration
     *
     * @param array $configuration
     * @param int $uid
     *
     * @return object
     */
    public static function getOriginalRecordByConfiguration($configuration, $uid)
    {
        $query = HelperUtility::getQuery($configuration['modelName']);
        $query->getQuerySettings()
            ->setRespectStoragePage(false);
        $query->matching($query->equals('uid', $uid));
        return $query->execute()
            ->getFirst();
    }
}
