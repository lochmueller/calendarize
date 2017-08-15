<?php
/**
 * Abstraction for loop view helper
 *
 */
namespace HDNET\Calendarize\ViewHelpers\Loop;

use HDNET\Calendarize\ViewHelpers\AbstractViewHelper;

/**
 * Abstraction for loop view helper
 *
 */
abstract class AbstractLoopViewHelper extends AbstractViewHelper
{


    /**
     * Specifies whether the escaping interceptors should be disabled or enabled for the result of renderChildren() calls within this ViewHelper
     * @see isChildrenEscapingEnabled()
     *
     * Note: If this is NULL the value of $this->escapingInterceptorEnabled is considered for backwards compatibility
     *
     * @var bool
     * @api
     */
    protected $escapeChildren = false;

    /**
     * Specifies whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper
     * @see isOutputEscapingEnabled()
     *
     * @var bool
     * @api
     */
    protected $escapeOutput = false;

    /**
     * Render the element
     *
     * @param \DateTime $date
     * @param string    $iteration
     *
     * @return string
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     */
    public function render(\DateTime $date, $iteration)
    {
        $variableContainer = $this->renderingContext->getTemplateVariableContainer();

        // clone: take care that the getItems method do not manipulate the original
        $items = $this->getItems(clone $date);

        $iterationData = [
            'index' => 0,
            'cycle' => 1,
            'total' => count($items)
        ];

        $output = '';
        foreach ($items as $item) {
            $iterationData['isFirst'] = $iterationData['cycle'] === 1;
            $iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
            $iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
            $iterationData['isOdd'] = !$iterationData['isEven'];
            $iterationData['calendar'] = $item;

            $variableContainer->add($iteration, $iterationData);

            $output .= $this->renderChildren();

            $variableContainer->remove($iteration);
            $iterationData['index']++;
            $iterationData['cycle']++;
        }
        return $output;
    }

    /**
     * Get the items
     *
     * @param \DateTime $date
     *
     * @return array
     */
    abstract protected function getItems(\DateTime $date);
}
