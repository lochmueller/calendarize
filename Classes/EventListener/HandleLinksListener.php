<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Register;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Frontend\Event\BeforeDatabaseRecordLinkResolvedEvent;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

class HandleLinksListener
{
    #[AsEventListener(
        identifier: 'calendarizeHandleLinks',
    )]
    public function __invoke(BeforeDatabaseRecordLinkResolvedEvent $event): void
    {
        if (isset($event->linkDetails['identifier']) && in_array($event->linkDetails['identifier'], array_column(Register::getRegister(), 'tableName'), true)) {
            $indexUid = $this->getIndexForEventUid($event->linkDetails['identifier'], (int)$event->linkDetails['uid']);
            if (!$indexUid) {
                throw new UnableToLinkException('Indices not found for "' . $event->linkDetails['typoLinkParameter'] . '".', 1699909349);
            }
            $event->record['index_uid'] = $indexUid;
        }
    }

    protected function getIndexForEventUid(string $table, int $uid): int
    {
        $indexRepository = GeneralUtility::makeInstance(IndexRepository::class);

        $fetchEvent = $indexRepository->findByTableAndUid($table, $uid, true, false, 1)->getFirst();
        if (null === $fetchEvent) {
            $fetchEvent = $indexRepository
                ->findByTableAndUid($table, $uid, false, true, 1, QueryInterface::ORDER_DESCENDING)
                ->getFirst();
        }

        return (int)$fetchEvent?->getUid();
    }
}
