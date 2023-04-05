<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Features;

use HDNET\Calendarize\Domain\Model\Index;

/**
 * Index interface for the ke_search extension to index the events.
 */
interface KeSearchIndexInterface
{
    public function getKeSearchTitle(Index $index): string;

    public function getKeSearchAbstract(Index $index): string;

    public function getKeSearchContent(Index $index): string;

    public function getKeSearchTags(Index $index): string;
}
