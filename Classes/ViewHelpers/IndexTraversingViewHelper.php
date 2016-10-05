<?php

/**
 * Index traversing
 */

namespace HDNET\Calendarize\ViewHelpers;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Index traversing
 */
class IndexTraversingViewHelper extends AbstractViewHelper
{

    /**
     * Render method
     *
     * @param Index  $index
     * @param bool   $future
     * @param bool   $past
     * @param int    $limit
     * @param string $sort
     *
     * @return array
     */
    public function render(
        Index $index,
        $future = true,
        $past = false,
        $limit = 100,
        $sort = QueryInterface::ORDER_ASCENDING
    ) {
        /** @var IndexRepository $indexRepository */
        $indexRepository = $this->objectManager->get(IndexRepository::class);
        return $indexRepository->findByTraversing($index, $future, $past, $limit, $sort);
    }
}
