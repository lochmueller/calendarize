<?php

/**
 * CalDav repository.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Domain\Model\CalDav;

/**
 * CalDav repository.
 */
class CalDavRepository extends AbstractRepository
{
    /**
     * Find the right CalDav configuration.
     *
     * @param int $pageId
     *
     * @return CalDav
     */
    public function findByUserStorage($pageId)
    {
        $query = $this->createQuery();
        $result = $query->execute(true);

        return $result[0] ?? false;
    }
}
