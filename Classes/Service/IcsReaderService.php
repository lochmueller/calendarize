<?php
/**
 * ICS Service
 *
 * @package Calendarize\Service
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use JMBTechnologyLimited\ICalDissect\ICalParser;
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
	 */
	function toArray($paramUrl) {
		$tempFileName = GeneralUtility::getFileAbsFileName('typo3temp/calendarize_temp_' . GeneralUtility::shortMD5($paramUrl));
		if (filemtime($tempFileName) < (time() - 60 * 60)) {
			$icsFile = GeneralUtility::getUrl($paramUrl);
			GeneralUtility::writeFile($tempFileName, $icsFile);
		}

		$backend = new ICalParser();
		if ($backend->parseFromFile($tempFileName)) {
			return $backend->getEvents();
		}
		return array();
	}
}