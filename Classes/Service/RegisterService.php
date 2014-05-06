<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Service;

use TYPO3\CMS\Core\Category\CategoryRegistry;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class RegisterService {

	/**
	 * @var array
	 */
	static protected $register = array();

	/**
	 * Add the given table name to the register
	 *
	 * @param string $tableName
	 */
	public function register($tableName) {
		CategoryRegistry::getInstance()
			->add('calendarize', 'tt_content', 'calender_categories');
		self::$register[] = $tableName;
	}

	/**
	 * Get the register
	 *
	 * @return array
	 */
	public function getRegister() {
		return self::$register;
	}

}
 