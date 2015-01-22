<?php
/**
 * Logical configuration group
 *
 * @category   Extension
 * @package    Calendarize
 * @subpackage Domain\Model
 * @author     Tim Lochmüller
 */

namespace HDNET\Calendarize\Domain\Model;

/**
 * Logical configuration group
 *
 * @package    Calendarize
 * @subpackage Domain\Model
 * @author     Tim Lochmüller
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
	 * Configurations
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HDNET\Calendarize\Domain\Model\Configuration>
	 * @db text NOT NULL
	 */
	protected $configurations;

	/**
	 * Set title
	 *
	 * @param string $title
	 *
	 * @return void
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

	/**
	 * Set configurations
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $configurations
	 */
	public function setConfigurations($configurations) {
		$this->configurations = $configurations;
	}

	/**
	 * Get configurations
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getConfigurations() {
		return $this->configurations;
	}

}
 