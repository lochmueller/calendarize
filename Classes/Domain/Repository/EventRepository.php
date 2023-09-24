<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Domain\Model\Dto\Search;
use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Model\Index;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Event repository.
 */
class EventRepository extends AbstractRepository
{
    protected IndexRepository $indexRepository;

    public function injectIndexRepository(IndexRepository $indexRepository): void
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * Get the IDs of the given search term.
     */
    public function findBySearch(Search $search): array
    {
        $query = $this->createQuery();
        $constraints = [];
        if ($search->getFullText()) {
            $constraints['fullText'] = $query->logicalOr(
                $query->like('title', '%' . $search->getFullText() . '%'),
                $query->like('description', '%' . $search->getFullText() . '%'),
            );
        }
        if ($search->getCategories()) {
            $categories = [];
            foreach ($search->getCategories() as $category) {
                $categories[] = $query->contains('categories', $category);
            }
            $constraints['categories'] = $query->logicalOr(...$categories);
        }
        $query->matching($query->logicalAnd(...$constraints));
        $rows = $query->execute(true);

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int)$row['uid'];
        }

        return $ids;
    }

    public function findOneByImportId(string $importId): ?object
    {
        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $querySettings->setIgnoreEnableFields(true);

        $query->matching($query->equals('importId', $importId));

        return $query->execute()->getFirst();
    }

    /**
     * Get the right Index ID by the event ID.
     */
    public function findNextIndex(int $uid): ?object
    {
        /** @var Event $event */
        $event = $this->findByUid($uid);

        if (!is_object($event)) {
            return null;
        }

        try {
            $result = $this->indexRepository->findByEventTraversing($event, true, false, 1);
            if (empty($result)) {
                $result = $this->indexRepository->findByEventTraversing(
                    $event,
                    false,
                    true,
                    1,
                    QueryInterface::ORDER_DESCENDING
                );
            }
        } catch (\Exception $exception) {
            return null;
        }

        if (empty($result)) {
            return null;
        }

        /** @var Index $index */
        return $result->getFirst();
    }
}
