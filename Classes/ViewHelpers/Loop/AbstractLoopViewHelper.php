<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Loop;

use HDNET\Calendarize\ViewHelpers\AbstractViewHelper;

/**
 * Abstraction for loop view helper.
 */
abstract class AbstractLoopViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Init arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('date', \DateTimeInterface::class, 'DateTimeInterface Object', true);
        $this->registerArgument('iteration', 'string', 'Iterator', true);
    }

    /**
     * Render the element.
     */
    public function render(): string
    {
        $variableContainer = $this->renderingContext->getVariableProvider();

        // clone: take care that the getItems method do not manipulate the original
        $items = $this->getItems(clone $this->arguments['date']);

        $iterationData = [
            'index' => 0,
            'cycle' => 1,
            'total' => \count($items),
        ];

        $output = '';
        foreach ($items as $item) {
            $iterationData['isFirst'] = 1 === $iterationData['cycle'];
            $iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
            $iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
            $iterationData['isOdd'] = !$iterationData['isEven'];
            $iterationData['calendar'] = $item;

            $variableContainer->add($this->arguments['iteration'], $iterationData);

            $output .= $this->renderChildren();

            $variableContainer->remove($this->arguments['iteration']);
            ++$iterationData['index'];
            ++$iterationData['cycle'];
        }

        return $output;
    }

    /**
     * Get the items.
     */
    abstract protected function getItems(\DateTime $date): array;
}
