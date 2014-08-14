<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\UserFunction;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * @todo       General class information
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

	function id2alias($value) {
		#DebuggerUtility::var_dump($value, 'ID');
		#die();
		return '--' . $value . '--';
	}

	function alias2id($value) {
		#DebuggerUtility::var_dump($value);
		#die();
		if (ereg('^--([0-9]+)--$', $value, $reg)) {
			return $reg[1];
		}
	}
}
 