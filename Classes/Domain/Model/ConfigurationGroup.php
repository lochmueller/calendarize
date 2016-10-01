<?php
/**
 * Logical configuration group
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Domain\Model;

/**
 * Logical configuration group
 *
 * @db
 * @smartExclude Language
 */
class ConfigurationGroup extends AbstractModel
{

    /**
     * Title
     *
     * @var string
     * @db
     */
    protected $title;

    /**
     * Configurations
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HDNET\Calendarize\Domain\Model\Configuration>
     * @db text
     */
    protected $configurations;

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
     * Get configurations
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Set configurations
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $configurations
     */
    public function setConfigurations($configurations)
    {
        $this->configurations = $configurations;
    }
}
