<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Configuration repository.
 */
class ConfigurationRepository extends AbstractRepository
{
    /**
     * Find by Index UIDs.
     */
    public function findByUids(array $uids): array|QueryResultInterface
    {
        $query = $this->createQuery();

        return $this->matchAndExecute($query, [
            $query->in('uid', $uids),
        ]);
    }
}
