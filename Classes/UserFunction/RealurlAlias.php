<?php
/**
 * RealURL alias
 *
 * @package Calendarize\UserFunction
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\UserFunction;

/**
 * RealURL alias
 *
 * @author Tim Lochmüller
 */
class RealurlAlias {

	/**
	 * Build the realurl alias
	 *
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
	 * Handle the alias to index ID convert
	 *
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

	/**
	 * Handle the index ID to alias convert
	 *
	 * @param $value
	 *
	 * @return string
	 */
	protected function id2alias($value) {
		return '--' . $value . '--';
	}
}