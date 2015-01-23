<?php
/**
 * @todo    General file information
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Loop;

use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * @todo   General class information
 *
 * @author Tim Lochmüller
 */
class WeeksInMonthViewHelper extends AbstractLoopViewHelper {

	/**
	 * @param \DateTime $date
	 * @param string    $iteration
	 *
	 * @return string
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 */
	public function render(\DateTime $date, $iteration) {
		return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * @param array                     $arguments
	 * @param \Closure                  $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 *
	 * @return string
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$templateVariableContainer = $renderingContext->getTemplateVariableContainer();
		if (!($arguments['date'] instanceof \DateTime)) {
			throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('You have to call the viewHelper with a valid date', 17832834234);
		}
		$iterationData = array(
			'index' => 0,
			'cycle' => 1,
			'total' => count($arguments['each'])
		);

		$weeks = array(
			'test'
		);

		$output = '';
		foreach ($weeks as $week) {
			$iterationData['isFirst'] = $iterationData['cycle'] === 1;
			$iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
			$iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
			$iterationData['isOdd'] = !$iterationData['isEven'];
			$templateVariableContainer->add($arguments['iteration'], $iterationData);

			$output .= $renderChildrenClosure();

			$templateVariableContainer->remove($arguments['iteration']);
			$iterationData['index']++;
			$iterationData['cycle']++;
		}
		return $output;
	}
}
