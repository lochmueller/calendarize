<?php

/**
 * Event repository.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Query;

/**
 * Event repository.
 */
class EventRepository extends AbstractRepository
{
    /**
     * Get the IDs of the given search term.
     *
     * @param string $searchTerm
     *
     * @return array
     */
    public function getIdsBySearchTerm($searchTerm)
    {
        $query = $this->createQuery();
        $constraint = [];
        $constraint[] = $query->like('title', '%' . $searchTerm . '%');
        $constraint[] = $query->like('description', '%' . $searchTerm . '%');
        $query->matching($query->logicalOr($constraint));
        $rows = $query->execute(true);

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) $row['uid'];
        }

        return $ids;
    }

    /**
     * Return the current tablename.
     *
     * @return string
     */
    public function getTableName()
    {
        $query = $this->createQuery();
        if ($query instanceof Query) {
            $source = $query->getSource();
            if (\method_exists($source, 'getSelectorName')) {
                return $source->getSelectorName();
            }
        }
    }
}
