<?php
/**
 * Feed interface
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Features;

/**
 * Feed interface
 */
interface FeedInterface
{

    /**
     * Get the feed title
     *
     * @return string
     */
    public function getFeedTitle();

    /**
     * Get the feed abstract
     *
     * @return string
     */
    public function getFeedAbstract();

    /**
     * Get the feed content
     *
     * @return string
     */
    public function getFeedContent();

    /**
     * Get the feed location
     *
     * @return string
     */
    public function getFeedLocation();
}
