<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Calendarize\Features\FeedInterface;
use HDNET\Calendarize\Features\KeSearchIndexInterface;
use HDNET\Calendarize\Features\SpeakingUrlInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Event (Default) for the calendarize function.
 *
 * @DatabaseTable
 */
class Event extends AbstractModel implements FeedInterface, SpeakingUrlInterface, KeSearchIndexInterface
{
    use ImportTrait;
    /**
     * Title.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $title = '';

    /**
     * Slug.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $slug = '';

    /**
     * Abstract / Teaser.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $abstract = '';

    /**
     * Description.
     *
     * @var string
     *
     * @DatabaseField("string")
     *
     * @EnableRichText
     */
    protected $description = '';

    /**
     * Location.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $location = '';

    /**
     * Location link.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $locationLink = '';

    /**
     * Organizer.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $organizer = '';

    /**
     * Organizer link.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $organizerLink = '';

    /**
     * Images.
     *
     * @var ObjectStorage<FileReference>
     *
     * @DatabaseField("\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>")
     *
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $images;

    /**
     * Downloads.
     *
     * @var ObjectStorage<FileReference>
     *
     * @DatabaseField("\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>")
     *
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $downloads;

    /**
     * Relation field. It is just used by the importer of the default events.
     * You do not need this field, if you don't use the default Event.
     *
     * @var ObjectStorage<Configuration>
     *
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $calendarize;

    /**
     * Categories.
     *
     * @var ObjectStorage<Category>
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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Get abstract.
     */
    public function getAbstract(): string
    {
        return $this->abstract;
    }

    /**
     * Set abstract.
     *
     * @param string $abstract
     */
    public function setAbstract(string $abstract): void
    {
        $this->abstract = $abstract;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get downloads.
     *
     * @return ObjectStorage<FileReference>
     */
    public function getDownloads(): ObjectStorage
    {
        return $this->downloads;
    }

    /**
     * Set downloads.
     *
     * @param ObjectStorage<FileReference> $downloads
     */
    public function setDownloads(ObjectStorage $downloads): void
    {
        $this->downloads = $downloads;
    }

    /**
     * Get images.
     *
     * @return ObjectStorage<FileReference>
     */
    public function getImages(): ObjectStorage
    {
        return $this->images;
    }

    /**
     * Set images.
     *
     * @param ObjectStorage $images
     */
    public function setImages(ObjectStorage $images): void
    {
        $this->images = $images;
    }

    /**
     * Get calendarize.
     *
     * @return ObjectStorage<Configuration>
     */
    public function getCalendarize(): ObjectStorage
    {
        return $this->calendarize;
    }

    /**
     * Set calendarize.
     *
     * @param ObjectStorage<Configuration> $calendarize
     */
    public function setCalendarize(ObjectStorage $calendarize): void
    {
        $this->calendarize = $calendarize;
    }

    /**
     * Add one calendarize configuration.
     *
     * @param Configuration $calendarize
     */
    public function addCalendarize(Configuration $calendarize): void
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
        return $this->getTitle();
    }

    /**
     * Get the feed abstract.
     *
     * @return string
     */
    public function getFeedAbstract(): string
    {
        return $this->getFeedContent();
    }

    /**
     * Get the feed content.
     *
     * @return string
     */
    public function getFeedContent(): string
    {
        return $this->getDescription();
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

        return $this->getLocation();
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
    public function addCategory(Category $category): void
    {
        $this->categories->attach($category);
    }

    /**
     * Removes a Category.
     *
     * @param Category $categoryToRemove The Category to be removed
     */
    public function removeCategory(Category $categoryToRemove): void
    {
        $this->categories->detach($categoryToRemove);
    }

    /**
     * Returns the categories.
     *
     * @return ObjectStorage<Category>
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * Sets the categories.
     *
     * @param ObjectStorage $categories
     */
    public function setCategories(ObjectStorage $categories): void
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
        return $this->getTitle() . ' - ' . BackendUtility::date($index->getStartDate()->getTimestamp());
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
        return $this->getDescription();
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
        return $this->getDescription();
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
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * Set location.
     *
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * Get organizer.
     *
     * @return string
     */
    public function getOrganizer(): string
    {
        return $this->organizer;
    }

    /**
     * Set organizer.
     *
     * @param string $organizer
     */
    public function setOrganizer(string $organizer): void
    {
        $this->organizer = $organizer;
    }

    /**
     * Is hidden.
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Set hidden.
     *
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * Get location link.
     *
     * @return string
     */
    public function getLocationLink(): string
    {
        return $this->locationLink;
    }

    /**
     * Set location link.
     *
     * @param string $locationLink
     */
    public function setLocationLink(string $locationLink): void
    {
        $this->locationLink = $locationLink;
    }

    /**
     * Get organizer link.
     *
     * @return string
     */
    public function getOrganizerLink(): string
    {
        return $this->organizerLink;
    }

    /**
     * Set organizer link.
     *
     * @param string $organizerLink
     */
    public function setOrganizerLink(string $organizerLink): void
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
