<?php
/**
 * Layout for BE content elements
 *
 * @package Calendarize\Service
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;


/**
 * Layout for BE content elements
 *
 * @author Tim Lochmüller
 */
class ContentElementLayoutService extends AbstractService {

	/**
	 * Title of the element
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * Table information
	 *
	 * @var array
	 */
	protected $table = array();

	/**
	 * Set the title
	 *
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
		$this->table = array();
	}

	/**
	 * Add one row to the table
	 *
	 * @param string $label
	 * @param mixed  $value
	 */
	public function addRow($label, $value) {
		$this->table[] = array(
			$label,
			$value
		);
	}

	/**
	 * Render the settings as table for Web>Page module
	 * System settings are displayed in mono font
	 *
	 * @return string
	 */
	public function render() {
		if (!$this->table) {
			return '';
		}
		$content = '<strong>' . $this->title . '</strong>';
		foreach ($this->table as $line) {
			$content .= '<strong>' . $line[0] . '</strong>' . ' ' . $line[1] . '<br />';
		}

		return '<pre style="white-space:normal">' . $content . '</pre>';
	}

}
