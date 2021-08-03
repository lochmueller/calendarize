<?php

/**
 * Event (Default) for the calendarize function.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\SmartExclude;
use HDNET\Calendarize\Features\FeedInterface;
use HDNET\Calendarize\Features\KeSearchIndexInterface;
use HDNET\Calendarize\Features\SpeakingUrlInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Event (Default) for the calendarize function.
 *
 * @DatabaseTable
 */
class Event extends AbstractModel implements FeedInterface, SpeakingUrlInterface, KeSearchIndexInterface
{
    /**
     * Title.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $title;

    /**
     * Slug.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $slug = '';

    /**
     * Abstract / Teaser.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $abstract;

    /**
     * Description.
     *
     * @var string
     * @DatabaseField("string")
     * @EnableRichText
     */
    protected $description;

    /**
     * Location.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $location;

    /**
     * Location link.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $locationLink;

    /**
     * Organizer.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $organizer;

    /**
     * Organizer link.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $organizerLink;

    /**
     * Import ID if the item is based on an ICS structure.
     *
     * @var string
     * @DatabaseField("string")
     */
    protected $importId;

    /**
     * Images.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @DatabaseField("\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>")
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $images;

    /**
     * Downloads.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @DatabaseField("\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>")
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $downloads;

    /**
     * Relation field. It is just used by the importer of the default events.
     * You do not need this field, if you don't use the default Event.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HDNET\Calendarize\Domain\Model\Configuration>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $calendarize;

    /**
     * Categories.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    protected $categories;

    /**
     * Hidden.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * Build up the object.
     */
    public function __construct()
    {
        $this->calendarize = new ObjectStorage();
        $this->images = new ObjectStorage();
        $this->downloads = new ObjectStorage();
        $this->categories = new ObjectStorage();
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get abstract.
     *
     * @return string
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Set abstract.
     *
     * @param string $abstract
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get downloads.
     *
     * @return ObjectStorage
     */
    public function getDownloads()
    {
        return $this->downloads;
    }

    /**
     * Set downloads.
     *
     * @param ObjectStorage $downloads
     */
    public function setDownloads($downloads)
    {
        $this->downloads = $downloads;
    }

    /**
     * Get images.
     *
     * @return ObjectStorage
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set images.
     *
     * @param ObjectStorage $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * Get Import ID.
     *
     * @return string
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set import ID.
     *
     * @param string $importId
     */
    public function setImportId($importId)
    {
        $this->importId = $importId;
    }

    /**
     * Get calendarize.
     *
     * @return ObjectStorage
     */
    public function getCalendarize()
    {
        return $this->calendarize;
    }

    /**
     * Set calendarize.
     *
     * @param ObjectStorage $calendarize
     */
    public function setCalendarize($calendarize)
    {
        $this->calendarize = $calendarize;
    }

    /**
     * Add one calendarize configuration.
     *
     * @param Configuration $calendarize
     */
    public function addCalendarize($calendarize)
    {
        $this->calendarize->attach($calendarize);
    }

    /**
     * Get the feed title.
     *
     * @return string
     */
    public function getFeedTitle(): string
    {
        return (string)$this->getTitle();
    }

    /**
     * Get the feed abstract.
     *
     * @return string
     */
    public function getFeedAbstract(): string
    {
        return (string)$this->getFeedContent();
    }

    /**
     * Get the feed content.
     *
     * @return string
     */
    public function getFeedContent(): string
    {
        return (string)$this->getDescription();
    }

    /**
     * Get the feed location.
     *
     * @return string
     */
    public function getFeedLocation(): string
    {
        if ($this->getLocationLink()) {
            return "{$this->getLocation()} ({$this->getLocationLink()})";
        }

        return (string)$this->getLocation();
    }

    /**
     * Get the base for the realurl alias.
     *
     * @return string
     */
    public function getRealUrlAliasBase(): string
    {
        return $this->getSlug() ?: $this->getTitle();
    }

    /**
     * Adds a Category.
     *
     * @param Category $category
     */
    public function addCategory(Category $category)
    {
        $this->categories->attach($category);
    }

    /**
     * Removes a Category.
     *
     * @param Category $categoryToRemove The Category to be removed
     */
    public function removeCategory(Category $categoryToRemove)
    {
        $this->categories->detach($categoryToRemove);
    }

    /**
     * Returns the categories.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Sets the categories.
     *
     * @param ObjectStorage $categories
     */
    public function setCategories(ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    /**
     * Get the title.
     *
     * @param Index $index
     *
     * @return string
     */
    public function getKeSearchTitle(Index $index): string
    {
        return (string)$this->getTitle() . ' - ' . BackendUtility::date($index->getStartDate()->getTimestamp());
    }

    /**
     * Get the abstract.
     *
     * @param Index $index
     *
     * @return string
     */
    public function getKeSearchAbstract(Index $index): string
    {
        return (string)$this->getDescription();
    }

    /**
     * Get the content.
     *
     * @param Index $index
     *
     * @return string
     */
    public function getKeSearchContent(Index $index): string
    {
        return (string)$this->getDescription();
    }

    /**
     * Get the tags.
     *
     * @param Index $index
     *
     * @return string Comma separated list of tags, e.g. '#syscat1#,#syscat2#'
     */
    public function getKeSearchTags(Index $index): string
    {
        $keSearchTags = [];
        if (empty($keSearchTags)) {
            foreach ($this->getCategories() as $category) {
                $keSearchTags[] = "#syscat{$category->getUid()}#";
            }
        }

        return implode(',', $keSearchTags);
    }

    /**
     * Get location.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set location.
     *
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get orginzer.
     *
     * @return string
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }

    /**
     * Set organizer.
     *
     * @param string $organizer
     */
    public function setOrganizer($organizer)
    {
        $this->organizer = $organizer;
    }

    /**
     * Is hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * Set hidden.
     *
     * @param bool $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * Get location link.
     *
     * @return string
     */
    public function getLocationLink()
    {
        return $this->locationLink;
    }

    /**
     * Set location link.
     *
     * @param string $locationLink
     */
    public function setLocationLink($locationLink)
    {
        $this->locationLink = $locationLink;
    }

    /**
     * Get organizer link.
     *
     * @return string
     */
    public function getOrganizerLink()
    {
        return $this->organizerLink;
    }

    /**
     * Set organizer link.
     *
     * @param string $organizerLink
     */
    public function setOrganizerLink($organizerLink)
    {
        $this->organizerLink = $organizerLink;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
