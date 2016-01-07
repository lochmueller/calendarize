<?php
/**
 * Event (Default) for the calendarize function
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Calendarize\Features\FeedInterface;
use HDNET\Calendarize\Features\RealUrlInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Event (Default) for the calendarize function
 *
 * @db
 */
class Event extends AbstractModel implements FeedInterface, RealUrlInterface
{

    /**
     * Title
     *
     * @var string
     * @db
     */
    protected $title;

    /**
     * Description
     *
     * @var string
     * @db
     * @enableRichText
     */
    protected $description;

    /**
     * Images
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @db
     */
    protected $images;

    /**
     * Downloads
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @db
     */
    protected $downloads;

    /**
     * Import ID if the item is based on an ICS structure
     *
     * @var string
     * @db
     */
    protected $importId;

    /**
     * Relation field. It is just used by the importer of the default events. You do not need this field, if you don't use the default Event
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HDNET\Calendarize\Domain\Model\Configuration>
     */
    protected $calendarize;

    /**
     * Build up the object
     */
    function __construct()
    {
        $this->calendarize = new ObjectStorage();
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get downloads
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getDownloads()
    {
        return $this->downloads;
    }

    /**
     * Set downloads
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $downloads
     */
    public function setDownloads($downloads)
    {
        $this->downloads = $downloads;
    }

    /**
     * Get images
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set images
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * Get Import ID
     *
     * @return string
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set import ID
     *
     * @param string $importId
     */
    public function setImportId($importId)
    {
        $this->importId = $importId;
    }

    /**
     * Get calendarize
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getCalendarize()
    {
        return $this->calendarize;
    }

    /**
     * Set calendarize
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $calendarize
     */
    public function setCalendarize($calendarize)
    {
        $this->calendarize = $calendarize;
    }

    /**
     * Add one calendarize configuration
     *
     * @param Configuration $calendarize
     */
    public function addCalendarize($calendarize)
    {
        $this->calendarize->attach($calendarize);
    }

    /**
     * Get the feed title
     *
     * @return string
     */
    public function getFeedTitle()
    {
        return $this->getTitle();
    }

    /**
     * Get the feed abstract
     *
     * @return string
     */
    public function getFeedAbstract()
    {
        return $this->getFeedContent();
    }

    /**
     * Get the feed content
     *
     * @return string
     */
    public function getFeedContent()
    {
        return $this->getDescription();
    }

    /**
     * Get the base for the realurl alias
     *
     * @return string
     */
    public function getRealUrlAliasBase()
    {
        return $this->getTitle();
    }
}