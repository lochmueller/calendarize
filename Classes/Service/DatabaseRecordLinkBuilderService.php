<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Register;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

class DatabaseRecordLinkBuilderService
{
    public function __construct(
        private readonly IndexRepository $indexRepository,
    ) {}

    public function prepareCalendarizeLink(array $linkDetails, ServerRequestInterface $request, string $linkText): void
    {
        if (!isset($linkDetails['identifier']) || !$this->isCalendarizeTable($linkDetails['identifier'])) {
            return;
        }

        $defaultPid = $this->getDefaultDetailPid($request);
        if ($defaultPid <= 0) {
            throw new \RuntimeException('You have to configure calendarize:defaultDetailPid to use the linkhandler function');
        }

        $indexUid = $this->resolveIndexUid($linkDetails['identifier'], (int)$linkDetails['uid']);
        if (!$indexUid) {
            throw new UnableToLinkException(
                'Indices not found for "' . ($linkDetails['typoLinkParameter'] ?? '') . '", so "' . $linkText . '" was not linked.',
                1699909349,
                null,
                $linkText
            );
        }

        $this->populateRecordLinkConfiguration($linkDetails['identifier'], $defaultPid, $indexUid, $request);
    }

    public function isCalendarizeTable(string $identifier): bool
    {
        return in_array($identifier, array_column(Register::getRegister(), 'tableName'), true);
    }

    public function getDefaultDetailPid(ServerRequestInterface $request): int
    {
        $typoScriptArray = $request->getAttribute('frontend.typoscript')?->getSetupArray() ?? [];

        return (int)($typoScriptArray['plugin.']['tx_calendarize.']['settings.']['defaultDetailPid'] ?? 0);
    }

    public function resolveIndexUid(string $table, int $uid): int
    {
        $fetchEvent = $this->indexRepository->findByTableAndUid($table, $uid, true, false, 1)->getFirst();
        if (null === $fetchEvent) {
            $fetchEvent = $this->indexRepository
                ->findByTableAndUid($table, $uid, false, true, 1, QueryInterface::ORDER_DESCENDING)
                ->getFirst();
        }

        return (int)$fetchEvent?->getUid();
    }

    public function populateRecordLinkConfiguration(string $eventTable, int $pageUid, int $indexUid, ServerRequestInterface $request): void
    {
        $frontendTypoScript = $request->getAttribute('frontend.typoscript');
        if (null === $frontendTypoScript) {
            return;
        }

        $typoScriptArray = $frontendTypoScript->getSetupArray() ?? [];
        $typoScriptArray['config.']['recordLinks.'][$eventTable . '.']['typolink.'] = [
            'parameter' => $pageUid,
            'additionalParams' => HttpUtility::buildQueryString([
                'tx_calendarize_calendar' => [
                    'index' => $indexUid,
                    'controller' => 'Calendar',
                    'action' => 'detail',
                ],
            ], '&'),
        ];
        $frontendTypoScript->setSetupArray($typoScriptArray);
    }
}
