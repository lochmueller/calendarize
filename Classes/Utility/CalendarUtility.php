<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Utility;

use HDNET\Calendarize\Service\RegisterService;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class CalendarUtility {

	/**
	 * Enable the calender functions for the given table name
	 *
	 * @param string $tableName
	 */
	static public function calendarize($tableName) {
		$register = new RegisterService();
		$register->register($tableName);
	}

}
 