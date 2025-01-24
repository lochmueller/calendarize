<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Index traversing.
 *
 * == Examples ==
 *
 * <code title="Traversing thru future and past occurings of the event">
 * {namespace c=HDNET\Calendarize\ViewHelpers}
 * <f:for each="{c:indexTraversing(index:'{index}', future: 1, past: 0, limit: 10, sort: 'ASC', useIndexTime: 1)}"
 *    as="futureEvent">
 *  <f:debug>{futureEvent}</f:debug>
 * </f:for>
 * </code>
 */
class IndexTraversingViewHelper extends AbstractViewHelper
{
    protected IndexRepository $indexRepository;

    public function injectIndexRepository(IndexRepository $indexRepository): void
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * Init arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('index', Index::class, '', true);
        $this->registerArgument('future', 'bool', '', false, true);
        $this->registerArgument('past', 'bool', '', false, false);
        $this->registerArgument('limit', 'int', '', false, 100);
        $this->registerArgument('sort', 'string', '', false, QueryInterface::ORDER_ASCENDING);
        $this->registerArgument('useIndexTime', 'bool', '', false, false);
    }

    /**
     * Render method.
     */
    public function render(): array|QueryResultInterface
    {
        return $this->indexRepository->findByTraversing(
            $this->arguments['index'],
            $this->arguments['future'],
            $this->arguments['past'],
            (int)$this->arguments['limit'],
            $this->arguments['sort'],
            (bool)$this->arguments['useIndexTime'],
        );
    }
}
