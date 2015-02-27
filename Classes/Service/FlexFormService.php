<?php
/**
 * Work on flexforms
 *
 * @package Calendarize\Service
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Work on flexforms
 *
 * @author Tim Lochmüller
 */
class FlexFormService extends AbstractService {

	/**
	 * Flex form data
	 *
	 * @var array
	 */
	protected $flexformData = array();

	/**
	 * oad the given flexform into the service
	 *
	 * @param string $xml
	 */
	public function load($xml) {
		$this->flexformData = GeneralUtility::xml2array($xml);
	}

	/**
	 * Check if the flexform get valid data
	 *
	 * @return bool
	 */
	public function isValid() {
		return is_array($this->flexformData) && isset($this->flexformData['data']);
	}

	/**
	 * Get field value from flexform configuration,
	 * including checks if flexform configuration is available
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
		$flexformData = $this->flexformData['data'];
		if (is_array($flexformData) && is_array($flexformData[$sheet]) && is_array($flexformData[$sheet]['lDEF']) && is_array($flexformData[$sheet]['lDEF'][$key]) && isset($flexformData[$sheet]['lDEF'][$key]['vDEF'])
		) {
			return $flexformData[$sheet]['lDEF'][$key]['vDEF'];
		}
		return NULL;
	}
}
