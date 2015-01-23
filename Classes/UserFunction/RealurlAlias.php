<?php
/**
 * RealURL alias
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller
 */

namespace HDNET\Calendarize\UserFunction;

/**
 * RealURL alias
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller
 */
class RealurlAlias {

	/**
	 * @param $params
	 * @param $ref
	 *
	 * @return string
	 */
	public function main($params, $ref) {
		if ($params['decodeAlias']) {
			return $this->alias2id($params['value']);
		}
		return $this->id2alias($params['value']);
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	protected function id2alias($value) {
		return '--' . $value . '--';
	}

	/**
	 * @param $value
	 *
	 * @return null
	 */
	protected function alias2id($value) {
		$matches = array();
		if (preg_match('/^--([0-9]+)--$/', $value, $matches)) {
			return $matches[1];
		}
		return NULL;
	}
}