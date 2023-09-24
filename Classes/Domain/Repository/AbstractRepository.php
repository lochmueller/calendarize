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
     * Set additional slot arguments.
     */
    public function setAdditionalSlotArguments(array $additionalSlotArguments): void
    {
        $this->additionalSlotArguments = $additionalSlotArguments;
    }
}
