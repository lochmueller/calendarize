<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Domain\Model\Dto\Search;
use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Event\IndexRepositoryFindBySearchEvent;
use HDNET\Calendarize\Register;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Filters Event records by the search query ('categories' and 'fullText').
 */
class SearchConstraintEventListener
{
    public function __construct(protected EventRepository $eventRepository) {}

    public function __invoke(IndexRepositoryFindBySearchEvent $event): void
    {
        if (!\in_array(Register::UNIQUE_REGISTER_KEY, $event->getIndexTypes(), true)) {
            return;
        }
        $foreignIds = $event->getForeignIds();
        if (!empty($foreignIds['tx_calendarize_domain_model_event'])) {
            // Skip if there are already ids (e.g. by other extensions)
            return;
        }

        $search = $this->getSearchDto($event);
        if (!$search->isSearch()) {
            return;
        }

        $searchTermIds = $this->eventRepository->findBySearch($search);
        // Blocks result (displaying no event) on no search match (empty id array)
        $searchTermIds[] = -1;

        $foreignIds['tx_calendarize_domain_model_event'] = $searchTermIds;
        $event->setForeignIds($foreignIds);
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
            @trigger_error(
                'Search request with the parameter \'category\' is deprecated. Use \'categories\' instead.',
                \E_USER_DEPRECATED,
            );
            $search->setCategories([(int)$customSearch['category']]);
        }

        return $search;
    }
}
