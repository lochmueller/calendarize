<?php
/**
 * Event utility
 *
 * @author  Carsten Biebricher
 */

namespace HDNET\Calendarize\Utility;

use HDNET\Calendarize\Domain\Model\PluginConfiguration;
use HDNET\Calendarize\Utility\HelperUtility;

/**
 * Event utility
 */
class EventUtility
{
    /**
     * Get the original record by configuration
     *
     * @param PluginConfiguration|array $configuration
     * @param int $uid
     *
     * @return object
     */
    public static function getOriginalRecordByConfiguration($configuration, $uid)
    {
        $modelName = '';
        if ($configuration instanceof PluginConfiguration) {
            $modelName = $configuration->getModelName();
        } else {
            $modelName = $configuration['modelName'];
        }

        $query = HelperUtility::getQuery($modelName);
        $query->getQuerySettings()
            ->setRespectStoragePage(false);
        $query->matching($query->equals('uid', $uid));
        return $query->execute()
            ->getFirst();
    }
}
