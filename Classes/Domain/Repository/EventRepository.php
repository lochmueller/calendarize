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
        $query->getQuerySettings()->setRespectStoragePage(false);

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

        return array_map(static fn($row) => (int)($row['_LOCALIZED_UID'] ?? $row['uid']), $rows);
    }

    public function findOneByImportId(string $importId, ?int $pid = null): ?object
    {
        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $querySettings->setIgnoreEnableFields(true);

        $constraints = [$query->equals('importId', $importId)];
        if (null !== $pid) {
            $constraints[] = $query->equals('pid', $pid);
        }

        $query->matching($query->logicalAnd(...$constraints));

        return $query->execute()->getFirst();
    }

    /**
     * Get the right Index ID by the event ID.
     */
    public function findNextIndex(int $uid): ?object
    {
        /** @var Event $event */
        $event = $this->findByUid($uid);

        if (!\is_object($event)) {
            return null;
        }

        try {
            $result = $this->indexRepository->findByEventTraversing($event, true, false, 1)->getFirst();
            if (null === $result) {
                $result = $this->indexRepository->findByEventTraversing(
                    $event,
                    false,
                    true,
                    1,
                    QueryInterface::ORDER_DESCENDING,
                )->getFirst();
            }
        } catch (\Exception $exception) {
            return null;
        }

        return $result;
    }
}
