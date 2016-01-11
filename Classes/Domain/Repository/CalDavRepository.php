<?php
/**
 * CalDav repository
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Domain\Model\CalDav;

/**
 * CalDav repository
 */
class CalDavRepository extends AbstractRepository
{

    /**
     * Find the right CalDav configuration
     *
     * @param int $pageId
     *
     * @return CalDav
     */
    public function findByUserStorage($pageId)
    {
        $query = $this->createQuery();
        $result = $query->execute(true);

        return isset($result[0]) ? $result[0] : false;
    }

}