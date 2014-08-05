<?php
/**
 * Logical configuration group
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Domain\Model
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */

namespace HDNET\Calendarize\Domain\Model;

/**
 * Logical configuration group
 *
 * @package    Calendarize
 * @subpackage Domain\Model
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 * @db
 */
class ConfigurationGroup extends AbstractModel {

	/**
	 * Title
	 *
	 * @var string
	 * @db
	 */
	protected $title;

	/**
	 * Set title
	 *
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

}
 