<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Domain\Model\Dto\Search;
use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Event\IndexRepositoryFindBySearchEvent;
use HDNET\Calendarize\Register;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

class DefaultEventSearchListener
{
    public function __construct(protected EventRepository $eventRepository)
    {
    }

    public function __invoke(IndexRepositoryFindBySearchEvent $event): void
    {
        if (!\in_array(Register::UNIQUE_REGISTER_KEY, $event->getIndexTypes(), true)) {
            return;
        }

        $search = $this->getSearchDto($event);

        if (!$search->isSearch()) {
            return;
        }

        /** @var Typo3QuerySettings $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->eventRepository->setDefaultQuerySettings($querySettings);
        $searchTermIds = $this->eventRepository->findBySearch($search);

        // Blocks result (displaying no event) on no search match (empty id array)
        $searchTermIds[] = -1;

        $indexIds = $event->getIndexIds();
        $indexIds['tx_calendarize_domain_model_event'] = $searchTermIds;
        $event->setIndexIds($indexIds);
    }

    protected function getSearchDto(IndexRepositoryFindBySearchEvent $event): Search
    {
        $customSearch = $event->getCustomSearch();

        $search = new Search();
        $search->setFullText(trim((string)($customSearch['fullText'] ?? '')));

        if (\is_array($customSearch['categories'] ?? '')) {
            $categories = array_map('intval', $customSearch['categories']);
            $search->setCategories($categories);
        } elseif (MathUtility::canBeInterpretedAsInteger($customSearch['category'] ?? '')) {
            // Fallback for previous mode
            $search->setCategories([(int)$customSearch['category']]);
        }

        return $search;
    }
}
