<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Typolink;

use HDNET\Calendarize\Service\DatabaseRecordLinkBuilderService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Typolink\DatabaseRecordLinkBuilder as BaseDatabaseRecordLinkBuilderAlias;
use TYPO3\CMS\Frontend\Typolink\LinkResultInterface;

if ((new Typo3Version())->getMajorVersion() >= 14) {
    /**
     * DatabaseRecordLinkBuilder for TYPO3 v14+ (readonly parent class).
     */
    readonly class DatabaseRecordLinkBuilder extends BaseDatabaseRecordLinkBuilderAlias
    {
        public function buildLink(array $linkDetails, array $configuration, ServerRequestInterface $request, string $linkText = ''): LinkResultInterface
        {
            GeneralUtility::makeInstance(DatabaseRecordLinkBuilderService::class)
                ->prepareCalendarizeLink($linkDetails, $request, $linkText);

            return parent::buildLink($linkDetails, $configuration, $request, $linkText);
        }
    }
} else {
    /**
     * DatabaseRecordLinkBuilder for TYPO3 v13 (non-readonly parent class).
     */
    class DatabaseRecordLinkBuilder extends BaseDatabaseRecordLinkBuilderAlias
    {
        public function buildLink(array $linkDetails, array $configuration, ServerRequestInterface $request, string $linkText = ''): LinkResultInterface
        {
            GeneralUtility::makeInstance(DatabaseRecordLinkBuilderService::class)
                ->prepareCalendarizeLink($linkDetails, $request, $linkText);

            return parent::buildLink($linkDetails, $configuration, $request, $linkText);
        }
    }
}
