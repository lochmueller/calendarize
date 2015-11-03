<?php

/**
 * Index interface for the ke_search extension to index the events
 */

namespace HDNET\Calendarize\Domain\Model;

/**
 * Index interface for the ke_search extension to index the events
 */
interface KeSearchIndexInterface
{
    /**
     * Get the title
     *
     * @param Index $index
     * @return string
     */
    public function getKeSearchTitle($index);

    /**
     * Get the abstract
     *
     * @param Index $index
     * @return string
     */
    public function getKeSearchAbstract($index);

    /**
     * Get the content
     *
     * @param Index $index
     * @return string
     */
    public function getKeSearchContent($index);
}