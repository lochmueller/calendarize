<?php

/**
 * Index interface for the ke_search extension to index the events.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Features;

use HDNET\Calendarize\Domain\Model\Index;

/**
 * Index interface for the ke_search extension to index the events.
 */
interface KeSearchIndexInterface
{
    /**
     * Get the title.
     *
     * @param Index $index
     *
     * @return string
     */
    public function getKeSearchTitle(Index $index): string;

    /**
     * Get the abstract.
     *
     * @param Index $index
     *
     * @return string
     */
    public function getKeSearchAbstract(Index $index): string;

    /**
     * Get the content.
     *
     * @param Index $index
     *
     * @return string
     */
    public function getKeSearchContent(Index $index): string;

    /**
     * Get the tags.
     *
     * @param Index $index
     *
     * @return string Comma separated list of tags, e.g. '#syscat1#,#syscat2#'
     */
    public function getKeSearchTags(Index $index): string;
}
