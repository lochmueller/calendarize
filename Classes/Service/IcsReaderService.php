<?php

/**
 * ICS Service.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Utility\DateTimeUtility;
use JMBTechnologyLimited\ICalDissect\ICalParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ICS Service.
 */
class IcsReaderService extends AbstractService
{
    /**
     * Get the ICS events in an array.
     *
     * @param string $paramUrl
     *
     * @return array
     */
    public function toArray($paramUrl)
    {
        $tempFileName = $this->getCachedUrlFile($paramUrl);
        $backend = new ICalParser();
        if ($backend->parseFromFile($tempFileName)) {
            return $backend->getEvents();
        }
        return [];
    }

    /**
     * Get cached URL file
     *
     * @param string $url
     * @return string
     */
    protected function getCachedUrlFile(string $url):string {
        $tempFileName = $this->getCheckedCacheFolder() . \md5($url);
        if (!\is_file($tempFileName) || \filemtime($tempFileName) < (\time() - DateTimeUtility::SECONDS_HOUR)) {
            $icsFile = GeneralUtility::getUrl($url);
            GeneralUtility::writeFile($tempFileName, $icsFile);
        }
        return $tempFileName;
    }

    /**
     * Return the cache folder and check if the folder exists.
     *
     * @return string
     */
    protected function getCheckedCacheFolder():string
    {
        $cacheFolder = GeneralUtility::getFileAbsFileName('typo3temp/var/transient/calendarize/');
        if (!\is_dir($cacheFolder)) {
            GeneralUtility::mkdir_deep($cacheFolder);
        }

        return $cacheFolder;
    }
}
