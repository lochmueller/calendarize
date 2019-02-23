<?php

/**
 * Event utility.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use HDNET\Calendarize\Domain\Model\PluginConfiguration;

/**
 * Event utility.
 */
class EventUtility
{
    /**
     * Get the original record by configuration.
     *
     * @param PluginConfiguration|array $configuration
     * @param int                       $uid
     *
     * @return object
     */
    public static function getOriginalRecordByConfiguration($configuration, int $uid)
    {
        if ($configuration instanceof PluginConfiguration) {
            $modelName = $configuration->getModelName();
        } else {
            $modelName = $configuration['modelName'];
        }

        $query = HelperUtility::getQuery($modelName);
        $query->getQuerySettings()
            ->setRespectStoragePage(false);
        $query->getQuerySettings()
            ->setRespectSysLanguage(false);
        $query->matching($query->equals('uid', $uid));

        return $query->execute()
            ->getFirst();
    }
}
