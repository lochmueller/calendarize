<?php
/**
 * Work on flex forms
 *
 * @package Calendarize\Service
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Work on flex forms
 *
 * @author Tim Lochmüller
 */
class FlexFormService extends AbstractService {

	/**
	 * Flex form data
	 *
	 * @var array
	 */
	protected $flexFormData = array();

	/**
	 * oad the given flex form into the service
	 *
	 * @param string $xml
	 */
	public function load($xml) {
		$this->flexFormData = GeneralUtility::xml2array($xml);
	}

	/**
	 * Get field value from flex form configuration,
	 * including checks if flex form configuration is available
	 *
	 * @param string $key   name of the key
	 * @param string $sheet name of the sheet
	 *
	 * @return string|NULL if nothing found, value if found
	 */
	public function get($key, $sheet = 'sDEF') {
		if (!$this->isValid()) {
			return NULL;
		}
		$flexFormData = $this->flexFormData['data'];
		if (is_array($flexFormData) && is_array($flexFormData[$sheet]) && is_array($flexFormData[$sheet]['lDEF']) && is_array($flexFormData[$sheet]['lDEF'][$key]) && isset($flexFormData[$sheet]['lDEF'][$key]['vDEF'])
		) {
			return $flexFormData[$sheet]['lDEF'][$key]['vDEF'];
		}
		return NULL;
	}

	/**
	 * Check if the flex form get valid data
	 *
	 * @return bool
	 */
	public function isValid() {
		return is_array($this->flexFormData) && isset($this->flexFormData['data']);
	}
}
