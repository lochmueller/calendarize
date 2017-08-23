<?php

/**
 * Index traversing.
 */

namespace HDNET\Calendarize\ViewHelpers;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Index traversing.
 *
 * == Examples ==
 *
 * <code title="Traversing thru future and past occurings of the event">
 * {namespace c=HDNET\Calendarize\ViewHelpers}
 * <f:for each="{c:indexTraversing(index:'{index}', future: 1, past: 0, limit: 10, sort: 'ASC', useIndexTime: 1)}" as="futureEvent">
 *  <f:debug>{futureEvent}</f:debug>
 * </f:for>
 * </code>
 */
class IndexTraversingViewHelper extends AbstractViewHelper
{
    /**
     * Render method.
     *
     * @param Index  $index
     * @param bool   $future
     * @param bool   $past
     * @param int    $limit
     * @param string $sort         ASC or DESC
     * @param bool   $useIndexTime use the Index-timestamp as base to look in the future or past
     *
     * @return array
     */
    public function render(
        Index $index,
        $future = true,
        $past = false,
        $limit = 100,
        $sort = QueryInterface::ORDER_ASCENDING,
        $useIndexTime = false
    ) {
        $indexRepository = $this->objectManager->get(IndexRepository::class);

        return $indexRepository->findByTraversing($index, $future, $past, (int) $limit, $sort, $useIndexTime);
    }
}
