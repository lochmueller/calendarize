<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Hooks;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class RealurlConfiguration {

	/**
	 * @param $params
	 * @param $pObj
	 *
	 * @return array
	 * @hook TYPO3_CONF_VARS|SC_OPTIONS|ext/realurl/class.tx_realurl_autoconfgen.php|extensionConfiguration
	 */
	function addCalendarizeConfiguration($params, &$pObj) {
		return array_merge_recursive($params['config'], array(
			'postVarSets' => array(
				'_DEFAULT' => array(
					'event' => array(
						array('GETvar' => 'tx_calendarize_calendar[action]'),
						array(
							'GETvar'   => 'tx_calendarize_calendar[index]',
							'userFunc' => 'EXT:calendarize/Classes/UserFunction/RealurlAlias.php:HDNET\\Calendarize\\UserFunction\\RealurlAlias->main'
						),
					),
					'page'  => array(
						array(
							'GETvar' => 'tx_calendarize_calendar[@widget_0][currentPage]',
						)
					)
				)
			)
		));
	}
}
 