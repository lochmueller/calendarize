<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Register;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Indices by object.
 *
 * == Examples ==
 *
 * <code title="Get all events of a custom model">
 * {namespace c=HDNET\Calendarize\ViewHelpers}
 * <f:for each="{c:indicesByObject(object:'{yourObject}', future: 1, past: 0, limit: 10, sort: 'ASC')}" as="futureEvent">
 *  <f:debug>{futureEvent}</f:debug>
 * </f:for>
 * </code>
 */
class IndicesByObjectViewHelper extends AbstractViewHelper
{
    /**
     * @var IndexRepository
     */
    protected $indexRepository;

    /**
     * @param IndexRepository $indexRepository
     */
    public function injectIndexRepository(IndexRepository $indexRepository)
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * Init arguments.
     */
    public function initializeArguments()
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
     *
     * @return array
     */
    public function render()
    {
        /** @var AbstractEntity $object */
        $object = $this->arguments['object'];

        $config = null;
        foreach (Register::getRegister() as $item) {
            if ($object instanceof $item['modelName']) {
                $config = $item;
                break;
            }
        }

        if (null === $config) {
            return [];
        }

        $fakeIndex = new Index();
        $fakeIndex->setForeignTable($config['tableName']);
        $fakeIndex->setForeignUid($object->_getProperty('_localizedUid') ?: $object->getUid());

        return $this->indexRepository->findByTraversing(
            $fakeIndex,
            $this->arguments['future'],
            $this->arguments['past'],
            (int)$this->arguments['limit'],
            $this->arguments['sort'],
            false
        );
    }
}
