<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Utility;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WorkspaceUtility
{
    public static function getCurrentWorkspaceId(): int
    {
        try {
            return (int)GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('workspace', 'id');
        } catch (\Exception $exception) {
        }

        return 0;
    }
}
