<?php
/**
 * Event repository
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Domain\Repository;

/**
 * Event repository
 */
class EventRepository extends AbstractRepository
{

    /**
     * Get the IDs of the given search term
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
            $ids[] = (int)$row['uid'];
        }
        return $ids;
    }
}
