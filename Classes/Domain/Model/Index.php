<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 * @db
 */
class Index extends AbstractEntity {

	/**
	 * @var string
	 * @db
	 */
	protected $tableName;

	/**
	 * @var string
	 * @db
	 */
	protected $identifier;

	/**
	 * @param string $identifier
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param string $tableName
	 */
	public function setTableName($tableName) {
		$this->tableName = $tableName;
	}

	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->tableName;
	}

}
 