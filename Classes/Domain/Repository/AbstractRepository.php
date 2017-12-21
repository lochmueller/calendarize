<?php

/**
 * Repository Abstraction.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository Abstraction.
 */
class AbstractRepository extends Repository
{
    /**
     * Additional slot arguments.
     *
     * @var array
     */
    protected $additionalSlotArguments = [];

    /**
     * Check the constraint and execute the query.
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

    /**
     * Set additional slot arguments.
     *
     * @param array $additionalSlotArguments
     */
    public function setAdditionalSlotArguments(array $additionalSlotArguments)
    {
        $this->additionalSlotArguments = $additionalSlotArguments;
    }

    /**
     * Call Signal incl. the additionalSlotArguments.
     *
     * @param string $signalClassName
     * @param string $signalName
     * @param array  $arguments
     *
     * @return mixed
     */
    protected function callSignal($signalClassName, $signalName, array $arguments)
    {
        $arguments['additionalSlotArguments'] = $this->additionalSlotArguments;
        $signalSlotDispatcher = HelperUtility::getSignalSlotDispatcher();

        return $signalSlotDispatcher->dispatch($signalClassName, $signalName, $arguments);
    }
}
