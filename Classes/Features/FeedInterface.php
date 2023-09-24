<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Features;

/**
 * Feed interface.
 */
interface FeedInterface
{
    public function getFeedTitle(): string;

    public function getFeedAbstract(): string;

    public function getFeedContent(): string;

    public function getFeedLocation(): string;
}
