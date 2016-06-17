<?php
/**
 * Repository Abstraction
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository Abstraction
 *
 * @author Tim Lochmüller
 */
class AbstractRepository extends Repository
{

    /**
     * Check the constraint and execute the query
     *
     * @param QueryInterface $query
     * @param array          $constraints
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function matchAndExecute(QueryInterface $query, array $constraints = [])
    {
        if (!empty($constraints)) {
            $query->matching($query->logicalAnd($constraints));
        }
        return $query->execute();
    }
}
