<?php
/**
 * Months in year view Helper
 *
 * @package Calendarize\ViewHelpers\Loop
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Loop;

use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Months in year view Helper
 *
 * @author Tim Lochmüller
 */
class MonthsInYearViewHelper extends AbstractLoopViewHelper {

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
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException
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

		$months = self::getMonthsOfYear($arguments['date']);

		$output = '';
		foreach ($months as $month) {
			$iterationData['isFirst'] = $iterationData['cycle'] === 1;
			$iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
			$iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
			$iterationData['isOdd'] = !$iterationData['isEven'];
			$iterationData['calendarWeek'] = $month['month'];
			$iterationData['calendarDate'] = $month['date'];

			$templateVariableContainer->add($arguments['iteration'], $iterationData);

			$output .= $renderChildrenClosure();

			$templateVariableContainer->remove($arguments['iteration']);
			$iterationData['index']++;
			$iterationData['cycle']++;
		}
		return $output;
	}

	/**
	 * @param \DateTime $date
	 *
	 * @return array
	 */
	static protected function getMonthsOfYear(\DateTime $date) {
		$months = array();
		$date->setDate($date->format('Y'), $date->format('n'), 1);
		for ($i = 0; $i < 12; $i++) {
			$months[$date->format('n')] = array(
				'week' => $date->format('n'),
				'date' => clone $date,
			);
			$date->modify('+1 month');
		}
		return $months;
	}
}