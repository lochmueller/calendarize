<?php

/**
 * Event utility.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use HDNET\Calendarize\Domain\Model\PluginConfiguration;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;

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
        if (self::isIgnoreEnableFields()) {
            $query->getQuerySettings()->setIgnoreEnableFields(true);
        }

        $query->getQuerySettings()
            ->setRespectStoragePage(false);
        $query->getQuerySettings()
            ->setRespectSysLanguage(false);
        $query->matching($query->equals('uid', $uid));

        return $query->execute()
            ->getFirst();
    }

    protected static function isIgnoreEnableFields(): bool
    {
        // Regular CLI requests
        if (Environment::isCli()) {
            return true;
        }

        // Modern (TYPO3_REQUEST) Backend request
        if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            return true;
        }

        // Old backend request (e.g. install tool wizards)
        if (\defined('TYPO3_MODE') && TYPO3_MODE === 'BE') {
            return true;
        }

        return false;
    }
}
