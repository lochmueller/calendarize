<?php
/**
 * ICS Service
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Utility\DateTimeUtility;
use JMBTechnologyLimited\ICalDissect\ICalParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ICS Service
 *
 * @author Tim Lochmüller
 */
class IcsReaderService extends AbstractService
{

    /**
     * Get the ICS events in an array
     *
     * @param string $paramUrl
     *
     * @return array
     */
    public function toArray($paramUrl)
    {
        $tempFileName = $this->getCheckedCacheFolder() . md5($paramUrl);
        if (!is_file($tempFileName) || filemtime($tempFileName) < (time() - DateTimeUtility::SECONDS_HOUR)) {
            $icsFile = GeneralUtility::getUrl($paramUrl);
            GeneralUtility::writeFile($tempFileName, $icsFile);
        }

        $backend = new ICalParser();
        if ($backend->parseFromFile($tempFileName)) {
            return $backend->getEvents();
        }
        return [];
    }

    /**
     * Return the cache folder and check if the folder exists
     *
     * @return string
     */
    protected function getCheckedCacheFolder()
    {
        $cacheFolder = GeneralUtility::getFileAbsFileName('typo3temp/calendarize/');
        if (!is_dir($cacheFolder)) {
            GeneralUtility::mkdir_deep($cacheFolder);
        }
        return $cacheFolder;
    }
}
