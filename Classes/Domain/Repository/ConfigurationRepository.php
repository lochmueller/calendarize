<?php

/**
 * Configuration repository.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

/**
 * Configuration repository.
 */
class ConfigurationRepository extends AbstractRepository
{
    /**
     * Find by Index UIDs.
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByUids(
        array $uids
    ) {
        $query = $this->createQuery();

        return $this->matchAndExecute($query, [
            $query->in('uid', $uids),
        ]);
    }
}
