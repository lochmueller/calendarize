<?php
/**
 * ICS Service
 *
 * @package Calendarize\Service
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ICS Service
 *
 * @author Tim Lochmüller
 */
class IcsReaderService extends AbstractService {

	/**
	 * Get the ICS events in an array
	 *
	 * @param string $paramUrl
	 *
	 * @return array
	 * @todo implement caching
	 */
	function toArray($paramUrl) {
		$icsFile = GeneralUtility::getUrl($paramUrl);
		$icsData = explode("BEGIN:", $icsFile);
		$icsDatesMeta = array();
		$icsDates = array();
		foreach ($icsData as $key => $value) {
			$icsDatesMeta[$key] = explode("\n", $value);
		}

		foreach ($icsDatesMeta as $key => $value) {
			foreach ($value as $subKey => $subValue) {
				if ($subValue != "") {
					if ($key != 0 && $subKey == 0) {
						$icsDates[$key]["BEGIN"] = $subValue;
					} else {
						$subValueArr = explode(":", $subValue, 2);
						$icsDates[$key][$subValueArr[0]] = $subValueArr[1];
					}
				}
			}
		}

		return $icsDates;
	}
}