<?php
/**
 * RealURL alias
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\UserFunction;

/**
 * RealURL alias
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class RealurlAlias {

	/**
	 * @param $params
	 * @param $ref
	 *
	 * @return string
	 */
	function main($params, $ref) {
		if ($params['decodeAlias']) {
			return $this->alias2id($params['value']);
		} else {
			return $this->id2alias($params['value']);
		}
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	function id2alias($value) {
		#DebuggerUtility::var_dump($value, 'ID');
		#die();
		return '--' . $value . '--';
	}

	/**
	 * @param $value
	 *
	 * @return null
	 */
	function alias2id($value) {
		#DebuggerUtility::var_dump($value);
		#die();
		$matches = array();
		if (preg_match('/^--([0-9]+)--$/', $value, $matches)) {
			return $matches[1];
		}
		return NULL;
	}
}
 