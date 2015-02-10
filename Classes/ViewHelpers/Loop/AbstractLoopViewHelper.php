<?php
/**
 * Abstraction for loop view helper
 *
 * @package Calendarize\ViewHelpers\Loop
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Loop;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Abstraction for loop view helper
 *
 * @author Tim Lochmüller
 */
abstract class AbstractLoopViewHelper extends AbstractViewHelper {

	/**
	 * Render the element
	 *
	 * @param \DateTime $date
	 * @param string    $iteration
	 *
	 * @return string
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 */
	public function render(\DateTime $date, $iteration) {
		$templateVariableContainer = $this->renderingContext->getTemplateVariableContainer();

		$items = $this->getItems($date);

		$iterationData = array(
			'index' => 0,
			'cycle' => 1,
			'total' => count($items)
		);

		$output = '';
		foreach ($items as $item) {
			$iterationData['isFirst'] = $iterationData['cycle'] === 1;
			$iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
			$iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
			$iterationData['isOdd'] = !$iterationData['isEven'];
			$iterationData['calendar'] = $item;

			$templateVariableContainer->add($iteration, $iterationData);

			$output .= $this->renderChildren();

			$templateVariableContainer->remove($iteration);
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
