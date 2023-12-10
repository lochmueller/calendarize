<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use HDNET\Calendarize\Domain\Repository\IndexRepository;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Indices by object.
 *
 * == Examples ==
 *
 * <code title="Get all events of a custom model">
 * {namespace c=HDNET\Calendarize\ViewHelpers}
 * <f:for each="{c:indicesByObject(object:'{yourObject}', future: 1, past: 0, limit: 10, sort: 'ASC')}"
 *   as="futureEvent">
 *  <f:debug>{futureEvent}</f:debug>
 * </f:for>
 * </code>
 */
class IndicesByObjectViewHelper extends AbstractViewHelper
{
    protected IndexRepository $indexRepository;

    /**
     * @param IndexRepository $indexRepository
     */
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
        $this->registerArgument('object', AbstractEntity::class, '', true);
        $this->registerArgument('future', 'bool', '', false, true);
        $this->registerArgument('past', 'bool', '', false, false);
        $this->registerArgument('limit', 'int', '', false, 100);
        $this->registerArgument('sort', 'string', '', false, QueryInterface::ORDER_ASCENDING);
    }

    /**
     * Render method.
     */
    public function render(): array|QueryResultInterface
    {
        /** @var AbstractEntity $object */
        $object = $this->arguments['object'];

        return $this->indexRepository->findByEventTraversing(
            $object,
            $this->arguments['future'],
            $this->arguments['past'],
            (int)$this->arguments['limit'],
            $this->arguments['sort'],
        );
    }
}
