<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service\Ical;

use HDNET\Calendarize\Exception\UnableToGetFileForUrlException;
use HDNET\Calendarize\Service\AbstractService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ICalUrlService extends AbstractService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var LinkService
     */
    protected LinkService $linkService;

    /**
     * @param LinkService $linkService
     */
    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    /**
     * Resolves an ics URL (t3://file?uid=23, https://example.com/calendar.ics) to a (temporary) local file.
     * Note: Always call GeneralUtility::unlink_tempfile($filename) afterwards!
     *
     * @param string $url URL
     *
     * @return string filename
     *
     * @throws UnableToGetFileForUrlException
     */
    public function getOrCreateLocalFileForUrl(?string $url): string
    {
        if (empty($url)) {
            throw new UnableToGetFileForUrlException('URL is empty', 1645464122);
        }
        $linkData = $this->linkService->resolve($url);
        switch ($linkData['type']) {
            case LinkService::TYPE_URL:
                $fileName = $this->downloadUrlToTemp($linkData['url']);
                break;
            case LinkService::TYPE_FILE:
                $file = $linkData['file'];
                if ($file instanceof File) {
                    $fileName = $file->getForLocalProcessing(false);
                } else {
                    $this->logger->error('Unable to find file "{url}"', ['url' => $url, 'linkData' => $linkData]);
                    throw new UnableToGetFileForUrlException("Unable to find file \"$url\"", 1645462637);
                }
                break;
            default:
                $this->logger->error(
                    'Unsupported link type "{type}"',
                    ['type' => $linkData['type'], 'linkData' => $linkData, 'url' => $url],
                );
                throw new UnableToGetFileForUrlException("Unsupported link type \"{$linkData['type']}\"", 1645462630);
        }
        $this->logger->info('Using file "{file}" for URL "{url}"', ['file' => $fileName, 'url' => $url]);

        return $fileName;
    }

    /**
     * Reads the contents of the URL and saves it in a temporary file.
     *
     * @throws UnableToGetFileForUrlException
     */
    protected function downloadUrlToTemp($url): string
    {
        $tempFileName = GeneralUtility::tempnam('calendarize-', 'ics');
        $icsFile = GeneralUtility::getUrl($url);
        if (false !== $icsFile) {
            GeneralUtility::writeFile($tempFileName, $icsFile);
        } else {
            GeneralUtility::unlink_tempfile($tempFileName);
            $this->logger->error('Unable to read the contents of the URL "{url}"', ['url' => $url]);
            throw new UnableToGetFileForUrlException("Unable to read the contents of the URL \"$url\"", 1645462308);
        }

        return $tempFileName;
    }
}
