<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository Abstraction.
 */
class AbstractRepository extends Repository
{
    /**
     * Additional slot arguments.
     */
    protected array $additionalSlotArguments = [];

    /**
     * Check the constraint and execute the query.
     */
    public function matchAndExecute(QueryInterface $query, array $constraints = []): array|QueryResultInterface
    {
        if (!empty($constraints)) {
            $query->matching($query->logicalAnd(...$constraints));
        }

        return $query->execute();
    }

    /**
     * Find the given records by UID, independent of storage page and language, but always
     * respecting the enable fields (hidden, starttime, endtime, fe_group) - also in backend
     * context. Since TYPO3 13.4.34 Extbase ignores enable fields in backend requests by default.
     *
     * @return object[]
     */
    public function findByUidsRespectingEnableFields(array $uids): array
    {
        $uids = array_values(array_filter(array_map('intval', $uids)));
        if (empty($uids)) {
            return [];
        }

        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setIgnoreEnableFields(false)
            ->setRespectStoragePage(false)
            ->setRespectSysLanguage(false);
        $query->matching($query->in('uid', $uids));

        return $query->execute()->toArray();
    }

    /**
     * Set additional slot arguments.
     */
    public function setAdditionalSlotArguments(array $additionalSlotArguments): void
    {
        $this->additionalSlotArguments = $additionalSlotArguments;
    }
}
