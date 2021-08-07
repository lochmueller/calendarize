<?php

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Event\IndexRepositoryFindBySearchEvent;
use HDNET\Calendarize\Register;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class DefaultEventSearchListener
{
    public function __invoke(IndexRepositoryFindBySearchEvent $event)
    {
        if (!\in_array(Register::UNIQUE_REGISTER_KEY, $event->getIndexTypes(), true)) {
            return;
        }

        $customSearch = $event->getCustomSearch();

        // Filter here for $customSearch['categories'] and take also care of the fullText
        // ?tx_calendarize_calendar[customSearch][categories]=1
        // https://github.com/lochmueller/calendarize/issues/89

        if (!isset($customSearch['fullText']) || !$customSearch['fullText']) {
            return;
        }
        /** @var EventRepository $eventRepository */
        $eventRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(EventRepository::class);
        $searchTermHits = $eventRepository->getIdsBySearchTerm($customSearch['fullText']);
        if ($searchTermHits && \count($searchTermHits)) {
            $indexIds = $event->getIndexIds();
            $indexIds['tx_calendarize_domain_model_event'] = $searchTermHits;
            $event->setIndexIds($indexIds);
        }
    }
}
