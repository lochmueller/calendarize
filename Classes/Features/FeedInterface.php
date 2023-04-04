<?php

/**
 * Feed interface.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Features;

/**
 * Feed interface.
 */
interface FeedInterface
{
    /**
     * Get the feed title.
     */
    public function getFeedTitle(): string;

    /**
     * Get the feed abstract.
     */
    public function getFeedAbstract(): string;

    /**
     * Get the feed content.
     */
    public function getFeedContent(): string;

    /**
     * Get the feed location.
     */
    public function getFeedLocation(): string;
}
