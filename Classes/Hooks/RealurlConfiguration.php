<?php
/**
 * Realurl configuration
 *
 * @package Calendarize\Hooks
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Hooks;

/**
 * Realurl configuration
 *
 * @author Tim Lochmüller
 */
class RealurlConfiguration {

	/**
	 * Add the realurl configuration example
	 *
	 * @param $params
	 * @param $pObj
	 *
	 * @return array
	 * @hook TYPO3_CONF_VARS|SC_OPTIONS|ext/realurl/class.tx_realurl_autoconfgen.php|extensionConfiguration
	 */
	public function addCalendarizeConfiguration($params, &$pObj) {
		return array_merge_recursive($params['config'], array(
			'postVarSets' => array(
				'_DEFAULT' => array(
					'event'      => array(
						array(
							'GETvar'   => 'tx_calendarize_calendar[index]',
							'userFunc' => 'HDNET\\Calendarize\\UserFunction\\RealurlAlias->main'
						),
					),
					'event-page' => array(
						array(
							'GETvar' => 'tx_calendarize_calendar[@widget_0][currentPage]',
						)
					)
				)
			)
		));
	}
}